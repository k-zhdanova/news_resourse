<?php

namespace App\Console\Commands;

use App\Http\Controllers\SearchController;
use Illuminate\Console\Command;

class InitSearchIndexes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'searchindex:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Инициализация поисковых индексов scout/tntsearch';

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
     * @return int
     */
    public function handle()
    {
        foreach (SearchController::MODELS as $model => $key) {
            $this->call('scout:import', [
                'model' => $model
            ]);
        }
    }
}
