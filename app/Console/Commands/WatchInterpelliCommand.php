<?php

namespace App\Console\Commands;

use App\Mail\DailyInterpelliMail;
use App\Models\NotifiedDays;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

class WatchInterpelliCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:watch-interpelli-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $baseUrl = 'https://win.istruzioneverona.it/uspvr/index.php/interpelli-docenti/interpelli-%s-%s/';
    protected $months = [
        '01' => 'gennaio',
        '02' => 'febbraio',
        '03' => 'marzo',
        '04' => 'aprile',
        '05' => 'maggio',
        '06' => 'giugno',
        '07' => 'luglio',
        '08' => 'agosto',
        '09' => 'settembre',
        '10' => 'ottobre',
        '11' => 'novembre',
        '12' => 'dicembre',
    ];

    public $to_email_addr = 'greggio.sara@gmail.com';
    public $to_name = 'Sara Greggio';
    public $cc_email_addr = 'evghenii.zubok@gmail.com';
    public $cc_name = 'Evghenii Zubok';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // it is worth it?
        $record = NotifiedDays::where('date', Carbon::now())->first();

        // if day was already notified ... lets get back to do nothing
        if ($record && $record->notified) {
            return;
        }

        // make url
        $month = Carbon::now()->format('m');
        $year = Carbon::now()->format('Y');
        $url = sprintf($this->baseUrl, $this->months[$month], $year);
        $browser = new HttpBrowser(HttpClient::create());
        $crawler = $browser->request('GET', $url);
        $todayInterpelliObjs = $crawler->filter('.entry-content ul li a');
        $todayInterpelli = [];

        foreach ($todayInterpelliObjs as $todayInterpelloObj) {
            $todayInterpelli[str_replace('Interpelli del ', '', $todayInterpelloObj->textContent)] = $todayInterpelloObj->getAttribute('href');
        }

        if (isset($todayInterpelli[Carbon::now()->format('d/m/Y')])) {

            // sending mail

            try {

                Mail::to($this->to_email_addr, $this->to_name)
                    ->cc($this->cc_email_addr, $this->cc_name)
                    ->send(new DailyInterpelliMail($todayInterpelli[Carbon::now()->format('d/m/Y')], $url, Carbon::now()->format('d/m/Y'), $this->to_name));

                // if it was delivered successfully then mark it as notified

                if ($record) {

                    $record->notified = true;
                    $record->error_msg = null;
                    $record->save();

                } else {

                    $record = new NotifiedDays();
                    $record->date = Carbon::now();
                    $record->notified = true;
                    $record->save();
                }

            } catch (\Exception $e) {

                if ($record) {

                    $record->notified = false;
                    $record->error_msg = $e->getMessage();
                    $record->save();

                } else {

                    $record = new NotifiedDays();
                    $record->date = Carbon::now();
                    $record->notified = false;
                    $record->error_msg = $e->getMessage();
                    $record->save();
                }
            }
        }
    }
}
