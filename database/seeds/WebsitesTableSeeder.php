<?php

use Illuminate\Database\Seeder;

class WebsitesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('websites')->insert([
            [
                'name' => 'RC2 Electronique',
                'url' => 'https://www.rc2.ch',
                'secret_key' => 'b684fejfwe993ierjfffdsf46g4sf5f6h9',
                'user_id' => '1'
            ],
            [
                'name' => 'Sara Oswald',
                'url' => 'http://www.saraoswald.ch',
                'secret_key' => 'b684fejfwe993ierjfffdsf46g4sf5f6h9',
                'user_id' => '1'
            ],
            [
                'name' => 'La Mariée',
                'url' => 'http://www.lamariee.ch',
                'secret_key' => 'rj4sf5f6h9ffb684fejfwe993iefdsf46g',
                'user_id' => '1'
            ],
            [
                'name' => 'Burgy Sàrl',
                'url' => 'http://burgy-charpente.ch',
                'secret_key' => '',
                'user_id' => '1'
            ],
        ]);
    }
}
