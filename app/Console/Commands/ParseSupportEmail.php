<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Extensions\EmailParsingUtil;

class ParseSupportEmail extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'support:parse-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parses incoming support email and creates/updates tickets.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $parsingUtil = new EmailParsingUtil();

//        $parsingUtil->readEmailFromFile(storage_path('app') . '/northstar_msg.txt');
        $parsingUtil->readEmailFromCommandPrompt();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(//			array('email', InputArgument::REQUIRED), //, 'An example argument.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(//			array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
        );
    }

}
