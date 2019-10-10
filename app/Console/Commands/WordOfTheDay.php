<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;


class WordOfTheDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'word:day';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'testing';

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
       DB::table('tag')->insert([
           'name' => rand(0,1000),
           'slug' => rand(0,1000),
           'description' => rand(0,1000)
       ]); 
    }
}
