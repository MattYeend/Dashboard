<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Industry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Company::exists()) {
            $this->command->info('Companies already seeded, skipping...');

            return;
        }

        $industries = Industry::pluck('id', 'code');

        if ($industries->isEmpty()) {
            $this->command->warn('No industries found, companies will be seeded without an industry...');
        }

        $accountManagers = User::inRandomOrder()->pluck('id');

        if ($accountManagers->isEmpty()) {
            $this->command->warn('No users found, companies will be seeded without an account manager...');
        }

        foreach ($this->getCompanies($industries) as $index => $company) {
            $company['account_manager_id'] = $accountManagers->isNotEmpty()
                ? $accountManagers[$index % $accountManagers->count()]
                : null;

            Company::create($company);
        }
    }

    /**
     * Get the predefined company records to seed.
     *
     * @param  Collection<string, int>  $industries
     * @return array<int, array<string, string|int|null>>
     */
    private function getCompanies(Collection $industries): array
    {
        return [
            [
                'name' => 'Brightwave Software Ltd',
                'slug' => 'brightwave-software-ltd',
                'email' => 'contact@brightwavesoftware.co.uk',
                'phone' => '+44 161 496 0142',
                'website' => 'https://www.brightwavesoftware.co.uk',
                'registration_number' => '09456213',
                'vat_number' => 'GB245789123',
                'description' => 'A Manchester-based software house specialising in bespoke business applications.',
                'industry_id' => $industries['62012'] ?? null,
                'employee_count' => 48,
                'founded_year' => 2011,
            ],
            [
                'name' => 'Thistle & Oak Retail Group',
                'slug' => 'thistle-oak-retail-group',
                'email' => 'info@thistleoak.co.uk',
                'phone' => '+44 131 226 5588',
                'website' => 'https://www.thistleoak.co.uk',
                'registration_number' => '07812934',
                'vat_number' => 'GB198765432',
                'description' => 'An Edinburgh-headquartered convenience retail chain with stores across Scotland.',
                'industry_id' => $industries['47110'] ?? null,
                'employee_count' => 620,
                'founded_year' => 1998,
            ],
            [
                'name' => 'Kestrel Build Contractors Ltd',
                'slug' => 'kestrel-build-contractors-ltd',
                'email' => 'enquiries@kestrelbuild.co.uk',
                'phone' => '+44 121 508 3321',
                'website' => 'https://www.kestrelbuild.co.uk',
                'registration_number' => '05678921',
                'vat_number' => 'GB176543298',
                'description' => 'A Birmingham commercial construction firm delivering office and retail developments.',
                'industry_id' => $industries['41200'] ?? null,
                'employee_count' => 210,
                'founded_year' => 2004,
            ],
            [
                'name' => 'Riverside Medical Practice Group',
                'slug' => 'riverside-medical-practice-group',
                'email' => 'admin@riversidemedical.nhs.uk',
                'phone' => '+44 191 232 7710',
                'website' => null,
                'registration_number' => '03214567',
                'vat_number' => null,
                'description' => 'A Newcastle general practice group providing primary care across three surgeries.',
                'industry_id' => $industries['86210'] ?? null,
                'employee_count' => 95,
                'founded_year' => 1987,
            ],
            [
                'name' => 'Sterling & Vane Merchant Bank',
                'slug' => 'sterling-vane-merchant-bank',
                'email' => 'contact@sterlingvane.co.uk',
                'phone' => '+44 20 7946 0192',
                'website' => 'https://www.sterlingvane.co.uk',
                'registration_number' => '02345671',
                'vat_number' => 'GB123456789',
                'description' => 'A City of London merchant bank offering corporate finance and lending services.',
                'industry_id' => $industries['64191'] ?? null,
                'employee_count' => 340,
                'founded_year' => 1962,
            ],
            [
                'name' => 'Cotswold Grain Farms Ltd',
                'slug' => 'cotswold-grain-farms-ltd',
                'email' => 'office@cotswoldgrain.co.uk',
                'phone' => '+44 1285 640 221',
                'website' => 'https://www.cotswoldgrain.co.uk',
                'registration_number' => '01123456',
                'vat_number' => 'GB412356789',
                'description' => 'A Gloucestershire arable farming business growing cereal and oil seed crops.',
                'industry_id' => $industries['01110'] ?? null,
                'employee_count' => 22,
                'founded_year' => 1954,
            ],
            [
                'name' => 'Cornish Catch Seafoods Ltd',
                'slug' => 'cornish-catch-seafoods-ltd',
                'email' => 'sales@cornishcatch.co.uk',
                'phone' => '+44 1326 314 872',
                'website' => 'https://www.cornishcatch.co.uk',
                'registration_number' => '02987654',
                'vat_number' => 'GB334455667',
                'description' => 'A Falmouth-based marine fishing operator supplying fresh catch to regional markets.',
                'industry_id' => $industries['03110'] ?? null,
                'employee_count' => 34,
                'founded_year' => 1976,
            ],
            [
                'name' => 'Humberside Meat Processors Ltd',
                'slug' => 'humberside-meat-processors-ltd',
                'email' => 'info@humbersidemeat.co.uk',
                'phone' => '+44 1482 613 490',
                'website' => 'https://www.humbersidemeat.co.uk',
                'registration_number' => '04456789',
                'vat_number' => 'GB556677889',
                'description' => 'A Hull-based meat processing and packing facility supplying UK supermarkets.',
                'industry_id' => $industries['10110'] ?? null,
                'employee_count' => 410,
                'founded_year' => 1982,
            ],
            [
                'name' => 'Pennine Brewing Co Ltd',
                'slug' => 'pennine-brewing-co-ltd',
                'email' => 'hello@penninebrewing.co.uk',
                'phone' => '+44 1274 552 018',
                'website' => 'https://www.penninebrewing.co.uk',
                'registration_number' => '06234567',
                'vat_number' => 'GB223344556',
                'description' => 'A Bradford craft brewery producing cask and keg beer for the Yorkshire trade.',
                'industry_id' => $industries['11050'] ?? null,
                'employee_count' => 29,
                'founded_year' => 2009,
            ],
            [
                'name' => 'Ironclad Steel Structures Ltd',
                'slug' => 'ironclad-steel-structures-ltd',
                'email' => 'contracts@ironcladsteel.co.uk',
                'phone' => '+44 114 276 3390',
                'website' => 'https://www.ironcladsteel.co.uk',
                'registration_number' => '05123478',
                'vat_number' => 'GB667788990',
                'description' => 'A Sheffield fabricator of structural steelwork for industrial and commercial builds.',
                'industry_id' => $industries['25110'] ?? null,
                'employee_count' => 156,
                'founded_year' => 1968,
            ],
            [
                'name' => 'Solent Circuit Technologies Ltd',
                'slug' => 'solent-circuit-technologies-ltd',
                'email' => 'sales@solentcircuit.co.uk',
                'phone' => '+44 23 8021 4456',
                'website' => 'https://www.solentcircuit.co.uk',
                'registration_number' => '08765432',
                'vat_number' => 'GB778899001',
                'description' => 'A Southampton manufacturer of printed circuit boards and electronic components.',
                'industry_id' => $industries['26110'] ?? null,
                'employee_count' => 88,
                'founded_year' => 1995,
            ],
            [
                'name' => 'Harrogate Data Centres Ltd',
                'slug' => 'harrogate-data-centres-ltd',
                'email' => 'support@harrogatedata.co.uk',
                'phone' => '+44 1423 566 210',
                'website' => 'https://www.harrogatedata.co.uk',
                'registration_number' => '10234567',
                'vat_number' => 'GB889900112',
                'description' => 'A Harrogate-based hosting provider offering colocation and managed server infrastructure.',
                'industry_id' => $industries['63110'] ?? null,
                'employee_count' => 65,
                'founded_year' => 2013,
            ],
            [
                'name' => 'Beaumont Legal Chambers Ltd',
                'slug' => 'beaumont-legal-chambers-ltd',
                'email' => 'clerks@beaumontlegal.co.uk',
                'phone' => '+44 113 246 7712',
                'website' => 'https://www.beaumontlegal.co.uk',
                'registration_number' => '09988776',
                'vat_number' => 'GB990011223',
                'description' => 'A Leeds solicitors\' practice advising on commercial property and corporate law.',
                'industry_id' => $industries['69102'] ?? null,
                'employee_count' => 52,
                'founded_year' => 1991,
            ],
            [
                'name' => 'Ashworth Consulting Group Ltd',
                'slug' => 'ashworth-consulting-group-ltd',
                'email' => 'enquiries@ashworthconsulting.co.uk',
                'phone' => '+44 20 3457 8821',
                'website' => 'https://www.ashworthconsulting.co.uk',
                'registration_number' => '07456123',
                'vat_number' => 'GB112233445',
                'description' => 'A London management consultancy advising mid-market businesses on operational strategy.',
                'industry_id' => $industries['70229'] ?? null,
                'employee_count' => 74,
                'founded_year' => 2003,
            ],
            [
                'name' => 'Fernlea Nursing Care Ltd',
                'slug' => 'fernlea-nursing-care-ltd',
                'email' => 'admissions@fernleacare.co.uk',
                'phone' => '+44 1244 678 902',
                'website' => 'https://www.fernleacare.co.uk',
                'registration_number' => '06678123',
                'vat_number' => null,
                'description' => 'A Chester-based provider of residential nursing care homes across Cheshire.',
                'industry_id' => $industries['87100'] ?? null,
                'employee_count' => 185,
                'founded_year' => 1989,
            ],
        ];
    }
}
