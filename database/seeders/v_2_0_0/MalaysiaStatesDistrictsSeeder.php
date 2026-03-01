<?php

namespace Database\Seeders\v_2_0_0;

use App\Model\helpdesk\Utility\District;
use App\Model\helpdesk\Utility\IpsType;
use App\Model\helpdesk\Utility\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MalaysiaStatesDistrictsSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        District::truncate();
        State::truncate();
        IpsType::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $states = [
            '01' => ['name' => 'Johor', 'districts' => [
                '01' => 'Batu Pahat', '02' => 'Johor Bahru', '03' => 'Kluang',
                '04' => 'Kota Tinggi', '05' => 'Kulai', '06' => 'Mersing',
                '07' => 'Muar', '08' => 'Pontian', '09' => 'Segamat', '10' => 'Tangkak',
            ]],
            '02' => ['name' => 'Kedah', 'districts' => [
                '01' => 'Baling', '02' => 'Bandar Baharu', '03' => 'Kota Setar',
                '04' => 'Kuala Muda', '05' => 'Kubang Pasu', '06' => 'Kulim',
                '07' => 'Langkawi', '08' => 'Padang Terap', '09' => 'Pendang',
                '10' => 'Pokok Sena', '11' => 'Sik', '12' => 'Yan',
            ]],
            '03' => ['name' => 'Kelantan', 'districts' => [
                '01' => 'Bachok', '02' => 'Gua Musang', '03' => 'Jeli',
                '04' => 'Kota Bharu', '05' => 'Kuala Krai', '06' => 'Machang',
                '07' => 'Pasir Mas', '08' => 'Pasir Puteh', '09' => 'Tanah Merah',
                '10' => 'Tumpat',
            ]],
            '04' => ['name' => 'Melaka', 'districts' => [
                '01' => 'Alor Gajah', '02' => 'Jasin', '03' => 'Melaka Tengah',
            ]],
            '05' => ['name' => 'Negeri Sembilan', 'districts' => [
                '01' => 'Jelebu', '02' => 'Jempol', '03' => 'Kuala Pilah',
                '04' => 'Port Dickson', '05' => 'Rembau', '06' => 'Seremban',
                '07' => 'Tampin',
            ]],
            '06' => ['name' => 'Pahang', 'districts' => [
                '01' => 'Bentong', '02' => 'Bera', '03' => 'Cameron Highlands',
                '04' => 'Jerantut', '05' => 'Kuantan', '06' => 'Lipis',
                '07' => 'Maran', '08' => 'Pekan', '09' => 'Raub',
                '10' => 'Rompin', '11' => 'Temerloh',
            ]],
            '07' => ['name' => 'Perak', 'districts' => [
                '01' => 'Batang Padang', '02' => 'Hilir Perak', '03' => 'Hulu Perak',
                '04' => 'Kampar', '05' => 'Kerian', '06' => 'Kinta',
                '07' => 'Kuala Kangsar', '08' => 'Larut, Matang dan Selama',
                '09' => 'Manjung', '10' => 'Muallim', '11' => 'Perak Tengah',
            ]],
            '08' => ['name' => 'Perlis', 'districts' => [
                '01' => 'Perlis',
            ]],
            '09' => ['name' => 'Pulau Pinang', 'districts' => [
                '01' => 'Barat Daya', '02' => 'Seberang Perai Selatan',
                '03' => 'Seberang Perai Tengah', '04' => 'Seberang Perai Utara',
                '05' => 'Timur Laut',
            ]],
            '10' => ['name' => 'Sabah', 'districts' => [
                '01' => 'Beaufort', '02' => 'Beluran', '03' => 'Keningau',
                '04' => 'Kinabatangan', '05' => 'Kota Belud', '06' => 'Kota Kinabalu',
                '07' => 'Kota Marudu', '08' => 'Kuala Penyu', '09' => 'Kudat',
                '10' => 'Kunak', '11' => 'Lahad Datu', '12' => 'Nabawan',
                '13' => 'Papar', '14' => 'Penampang', '15' => 'Pitas',
                '16' => 'Putatan', '17' => 'Ranau', '18' => 'Sandakan',
                '19' => 'Semporna', '20' => 'Sipitang', '21' => 'Tambunan',
                '22' => 'Tawau', '23' => 'Tenom', '24' => 'Tongod', '25' => 'Tuaran',
            ]],
            '11' => ['name' => 'Sarawak', 'districts' => [
                '01' => 'Betong', '02' => 'Bintulu', '03' => 'Kapit',
                '04' => 'Kuching', '05' => 'Limbang', '06' => 'Miri',
                '07' => 'Mukah', '08' => 'Samarahan', '09' => 'Sarikei',
                '10' => 'Serian', '11' => 'Sibu', '12' => 'Sri Aman',
            ]],
            '12' => ['name' => 'Selangor', 'districts' => [
                '01' => 'Gombak', '02' => 'Hulu Langat', '03' => 'Hulu Selangor',
                '04' => 'Klang', '05' => 'Kuala Langat', '06' => 'Kuala Selangor',
                '07' => 'Petaling', '08' => 'Sabak Bernam', '09' => 'Sepang',
            ]],
            '13' => ['name' => 'Terengganu', 'districts' => [
                '01' => 'Besut', '02' => 'Dungun', '03' => 'Hulu Terengganu',
                '04' => 'Kemaman', '05' => 'Kuala Nerus', '06' => 'Kuala Terengganu',
                '07' => 'Marang', '08' => 'Setiu',
            ]],
            '14' => ['name' => 'W.P. Kuala Lumpur', 'districts' => [
                '01' => 'Kuala Lumpur',
            ]],
            '15' => ['name' => 'W.P. Labuan', 'districts' => [
                '01' => 'Labuan',
            ]],
            '16' => ['name' => 'W.P. Putrajaya', 'districts' => [
                '01' => 'Putrajaya',
            ]],
        ];

        foreach ($states as $stateCode => $stateData) {
            $state = State::create([
                'code' => $stateCode,
                'name' => $stateData['name'],
            ]);

            foreach ($stateData['districts'] as $districtCode => $districtName) {
                District::create([
                    'code'     => $districtCode,
                    'name'     => $districtName,
                    'state_id' => $state->id,
                ]);
            }
        }

        $ipsTypes = [
            ['name' => 'Sekolah', 'sort' => 1],
            ['name' => 'Tadika', 'sort' => 2],
            ['name' => 'Pusat', 'sort' => 3],
        ];

        foreach ($ipsTypes as $type) {
            IpsType::create($type);
        }
    }
}
