<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\LocalGovernment;
use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $this->seedNigeria();
        $this->seedKenya();
    }

    private function seedNigeria(): void
    {
        $ng = Country::updateOrCreate(['iso2' => 'NG'], [
            'iso3' => 'NGA',
            'name' => 'Nigeria',
            'slug' => 'nigeria',
            'currency_code' => 'NGN',
            'currency_symbol' => '₦',
            'region_label' => 'State',
            'local_government_label' => 'Local Government Area',
            'identity_scheme' => 'ng-nin-bvn',
            'is_active' => true,
        ]);

        // 36 states + FCT. Sample of LGAs per state — enough for demos;
        // production seeds the full 774.
        $states = [
            'Abia' => ['Umuahia North', 'Aba North', 'Aba South'],
            'Adamawa' => ['Yola North', 'Yola South', 'Mubi North'],
            'Akwa Ibom' => ['Uyo', 'Ikot Ekpene', 'Eket'],
            'Anambra' => ['Awka North', 'Awka South', 'Onitsha North'],
            'Bauchi' => ['Bauchi', 'Misau', 'Azare'],
            'Bayelsa' => ['Yenagoa', 'Brass', 'Nembe'],
            'Benue' => ['Makurdi', 'Gboko', 'Otukpo'],
            'Borno' => ['Maiduguri', 'Jere', 'Konduga'],
            'Cross River' => ['Calabar Municipal', 'Calabar South', 'Akpabuyo'],
            'Delta' => ['Warri South', 'Warri North', 'Asaba'],
            'Ebonyi' => ['Abakaliki', 'Afikpo North', 'Ezza North'],
            'Edo' => ['Oredo', 'Egor', 'Ikpoba-Okha'],
            'Ekiti' => ['Ado Ekiti', 'Ikere', 'Ijero'],
            'Enugu' => ['Enugu North', 'Enugu East', 'Nsukka'],
            'Gombe' => ['Gombe', 'Akko', 'Yamaltu/Deba'],
            'Imo' => ['Owerri Municipal', 'Owerri North', 'Owerri West'],
            'Jigawa' => ['Dutse', 'Hadejia', 'Birnin Kudu'],
            'Kaduna' => ['Kaduna North', 'Kaduna South', 'Zaria'],
            'Kano' => ['Kano Municipal', 'Nasarawa', 'Fagge'],
            'Katsina' => ['Katsina', 'Daura', 'Funtua'],
            'Kebbi' => ['Birnin Kebbi', 'Argungu', 'Yauri'],
            'Kogi' => ['Lokoja', 'Okene', 'Idah'],
            'Kwara' => ['Ilorin West', 'Ilorin East', 'Ilorin South'],
            'Lagos' => ['Ikeja', 'Eti Osa', 'Lagos Mainland', 'Lagos Island', 'Surulere', 'Alimosho', 'Apapa'],
            'Nasarawa' => ['Lafia', 'Karu', 'Keffi'],
            'Niger' => ['Minna', 'Suleja', 'Bida'],
            'Ogun' => ['Abeokuta North', 'Abeokuta South', 'Ijebu Ode'],
            'Ondo' => ['Akure North', 'Akure South', 'Owo'],
            'Osun' => ['Osogbo', 'Ife Central', 'Ilesa West'],
            'Oyo' => ['Ibadan North', 'Ibadan South-West', 'Oluyole'],
            'Plateau' => ['Jos North', 'Jos South', 'Jos East'],
            'Rivers' => ['Port Harcourt', 'Obio-Akpor', 'Eleme'],
            'Sokoto' => ['Sokoto North', 'Sokoto South', 'Wamakko'],
            'Taraba' => ['Jalingo', 'Wukari', 'Bali'],
            'Yobe' => ['Damaturu', 'Potiskum', 'Nguru'],
            'Zamfara' => ['Gusau', 'Kaura Namoda', 'Talata Mafara'],
            'FCT' => ['Abuja Municipal', 'Bwari', 'Gwagwalada', 'Kuje'],
        ];

        foreach ($states as $stateName => $lgas) {
            $state = State::updateOrCreate(
                ['country_id' => $ng->id, 'slug' => Str::slug($stateName)],
                ['name' => $stateName],
            );

            foreach ($lgas as $lgaName) {
                LocalGovernment::updateOrCreate(
                    ['state_id' => $state->id, 'slug' => Str::slug($lgaName)],
                    ['name' => $lgaName],
                );
            }
        }
    }

    private function seedKenya(): void
    {
        $ke = Country::updateOrCreate(['iso2' => 'KE'], [
            'iso3' => 'KEN',
            'name' => 'Kenya',
            'slug' => 'kenya',
            'currency_code' => 'KES',
            'currency_symbol' => 'KSh',
            'region_label' => 'County',
            'local_government_label' => 'Sub-county',
            'identity_scheme' => 'ke-id',
            'is_active' => true,
        ]);

        // Sample only — proves the white-label angle. Real deployments
        // would seed all 47 counties.
        $counties = [
            'Nairobi' => ['Westlands', 'Dagoretti North', 'Embakasi East'],
            'Mombasa' => ['Mvita', 'Kisauni', 'Nyali'],
            'Kisumu' => ['Kisumu Central', 'Kisumu East', 'Kisumu West'],
        ];

        foreach ($counties as $countyName => $subs) {
            $county = State::updateOrCreate(
                ['country_id' => $ke->id, 'slug' => Str::slug($countyName)],
                ['name' => $countyName],
            );

            foreach ($subs as $sub) {
                LocalGovernment::updateOrCreate(
                    ['state_id' => $county->id, 'slug' => Str::slug($sub)],
                    ['name' => $sub],
                );
            }
        }
    }
}
