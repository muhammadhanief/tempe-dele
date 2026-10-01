<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MPejabatTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('m_pejabat')->delete();
        
        \DB::table('m_pejabat')->insert(array (
            0 => 
            array (
                'id_pejabat' => 8,
                'nama' => 'Dr. Ali Said MA.',
                'jabatan' => 'Kepala BPS',
                'nip_lama' => '340013014',
                'nip' => '196808291991121001',
                'status' => 'aktif',
                'tahun' => '2026',
            ),
            1 => 
            array (
                'id_pejabat' => 9,
                'nama' => 'Suci Budi Utami SST, M.Si',
                'jabatan' => 'PPK',
                'nip_lama' => '340016124',
                'nip' => '197811262000122001',
                'status' => 'aktif',
                'tahun' => '2026',
            ),
            2 => 
            array (
                'id_pejabat' => 12,
                'nama' => 'Joko Suwarjo S.Si, M.Si',
                'jabatan' => 'Kepala Bagian Umum',
                'nip_lama' => '340013741',
                'nip' => '197106131993121001',
                'status' => 'aktif',
                'tahun' => '2026',
            ),
            3 => 
            array (
                'id_pejabat' => 14,
                'nama' => 'VADSJN',
                'jabatan' => 'Kepala Bagian Umum',
                'nip_lama' => '93827',
                'nip' => '196911261989031001',
                'status' => 'aktif',
                'tahun' => '2026',
            ),
        ));
        
        
    }
}