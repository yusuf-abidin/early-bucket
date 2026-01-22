<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CabangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'area_name' => 'JAKARTA 1',
                'branches' => [
                    ['name' => 'JAKARTA HARMONI'],
                    ['name' => 'TANGERANG'],
                    ['name' => 'CIPUTAT'],
                    ['name' => 'CILEGON'],
                    ['name' => 'JAKARTA KEBON JERUK'],
                    ['name' => 'KARAWACI'],
                    ['name' => 'BUMI SERPONG DAMAI'],
                    ['name' => 'JAKARTA PLUIT'],
                    ['name' => 'BINTARO JAYA'],
                    ['name' => 'KELAPA GADING SQUARE'],
                    ['name' => 'AGUNG SEDAYU'],
                ],
            ],
            [
                'area_name' => 'JAKARTA II',
                'branches' => [
                    ['name' => 'BOGOR'],
                    ['name' => 'CIBINONG'],
                    ['name' => 'CIBUBUR'],
                    ['name' => 'DEPOK'],
                    ['name' => 'JAKARTA CAWANG'],
                    ['name' => 'JAKARTA KUNINGAN'],
                    ['name' => 'JAKARTA MELAWAI'],
                    ['name' => 'SUKABUMI'],
                ]
            ],
            [
                'area_name' => 'JATENG DIY',
                'branches' => [
                    ['name' => 'KUDUS'],
                    ['name' => 'MAGELANG'],
                    ['name' => 'PEKALONGAN'],
                    ['name' => 'PURWOKERTO'],
                    ['name' => 'SEMARANG'],
                    ['name' => 'SOLO'],
                    ['name' => 'TEGAL'],
                    ['name' => 'YOGYAKARTA'],
                ]
            ],
            [
                'area_name' => 'JATIM BALI NUSRA',
                'branches' => [
                    ['name' => 'BALI SELATAN'],
                    ['name' => 'BALI UTARA'],
                    ['name' => 'BANGKALAN'],
                    ['name' => 'BANYUWANGI'],
                    ['name' => 'GRESIK SELATAN'],
                    ['name' => 'GRESIK UTARA'],
                    ['name' => 'JEMBER'],
                    ['name' => 'KEDIRI'],
                    ['name' => 'KUPANG'],
                    ['name' => 'LAMONGAN'],
                    ['name' => 'MADIUN'],
                    ['name' => 'MALANG'],
                    ['name' => 'MATARAM'],
                    ['name' => 'MOJOKERTO'],
                    ['name' => 'PASURUAN'],
                    ['name' => 'PROBOLINGGO'],
                    ['name' => 'SIDOARJO'],
                    ['name' => 'SURABAYA'],
                ]
            ],
            [
                'area_name' => 'JAWA BARAT I',
                'branches' => [
                    ['name' => 'BEKASI'],
                    ['name' => 'CIKARANG'],
                    ['name' => 'HARAPAN INDAH'],
                    ['name' => 'KARAWANG'],
                ]
            ],
            [
                'area_name' => 'JAWA BARAT II',
                'branches' => [
                    ['name' => 'BANDUNG'],
                    ['name' => 'BANDUNG TIMUR'],
                    ['name' => 'CIMAHI'],
                    ['name' => 'CIREBON'],
                    ['name' => 'PURWAKARTA'],
                    ['name' => 'TASIKMALAYA'],
                ]
            ],
            [
                'area_name' => 'KALIMANTAN',
                'branches' => [
                    ['name' => 'BALIKPAPAN'],
                    ['name' => 'BANJARBARU'],
                    ['name' => 'BANJARMASIN'],
                    ['name' => 'PALANGKARAYA'],
                    ['name' => 'PONTIANAK'],
                    ['name' => 'SAMARINDA'],
                    ['name' => 'TARAKAN'],
                ]
            ],
            [
                'area_name' => 'SULAWESI MALUKU PAPUA',
                'branches' => [
                    ['name' => 'AMBON'],
                    ['name' => 'GORONTALO'],
                    ['name' => 'JAYAPURA'],
                    ['name' => 'KENDARI'],
                    ['name' => 'MAKASSAR'],
                    ['name' => 'MAMUJU'],
                    ['name' => 'MANADO'],
                    ['name' => 'PALU'],
                    ['name' => 'PANAKUKANG'],
                    ['name' => 'PARE-PARE'],
                    ['name' => 'TERNATE'],
                ]
            ],
            [
                'area_name' => 'SUMATERA I',
                'branches' => [
                    ['name' => 'BATAM'],
                    ['name' => 'KFO BANDA ACEH'],
                    ['name' => 'MEDAN'],
                    ['name' => 'PEKANBARU'],
                    ['name' => 'PEMATANG SIANTAR'],
                    ['name' => 'TANJUNG PINANG'],
                ]
            ],
            [
                'area_name' => 'SUMATERA II',
                'branches' => [
                    ['name' => 'BANDAR LAMPUNG'],
                    ['name' => 'BENGKULU'],
                    ['name' => 'JAMBI'],
                    ['name' => 'PADANG'],
                    ['name' => 'PALEMBANG'],
                    ['name' => 'PANGKALPINANG'],
                ]
            ],
        ];
    }
}
