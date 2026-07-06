<?php

namespace Database\Seeders;

use App\Models\Industry;
use Illuminate\Database\Seeder;

class IndustrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Industry::exists()) {
            $this->command->info('Industries already seeded, skipping...');

            return;
        }

        foreach ($this->getIndustries() as $industry) {
            Industry::factory()->create($industry);
        }
    }

    /**
     * Get the predefined real UK SIC 2007 industry records to seed,
     * sourced from the Companies House SIC code list.
     *
     * @return array<int, array<string, string|null>>
     */
    private function getIndustries(): array
    {
        return [
            [
                'title' => 'Growing of cereals (except rice), leguminous crops and oil seeds',
                'code' => '01110',
                'description' => 'Cultivation of cereal crops, leguminous crops and oil seeds, excluding rice.',
            ],
            [
                'title' => 'Raising of sheep and goats',
                'code' => '01450',
                'description' => 'Farming activities involving the raising and breeding of sheep and goats.',
            ],
            [
                'title' => 'Marine fishing',
                'code' => '03110',
                'description' => 'Commercial fishing activities carried out in coastal or deep sea waters.',
            ],
            [
                'title' => 'Extraction of crude petroleum',
                'code' => '06100',
                'description' => 'Extraction of crude petroleum from onshore or offshore sources.',
            ],
            [
                'title' => 'Processing and preserving of meat',
                'code' => '10110',
                'description' => 'Slaughtering, processing and preserving of meat products.',
            ],
            [
                'title' => 'Manufacture of beer',
                'code' => '11050',
                'description' => 'Brewing and manufacture of beer and related malt products.',
            ],
            [
                'title' => 'Preparation and spinning of textile fibres',
                'code' => '13100',
                'description' => 'Preparation, carding and spinning of natural and synthetic textile fibres.',
            ],
            [
                'title' => 'Manufacture of other products of wood',
                'code' => '16290',
                'description' => 'Manufacture of wood products not classified elsewhere, such as fittings and containers.',
            ],
            [
                'title' => 'Printing not elsewhere classified',
                'code' => '18129',
                'description' => 'Commercial printing activities not otherwise classified.',
            ],
            [
                'title' => 'Manufacture of dyes and pigments',
                'code' => '20120',
                'description' => 'Manufacture of dyes, pigments and related colouring agents.',
            ],
            [
                'title' => 'Manufacture of concrete products for construction purposes',
                'code' => '23610',
                'description' => 'Production of pre-cast concrete products used in construction.',
            ],
            [
                'title' => 'Manufacture of metal structures and parts of structures',
                'code' => '25110',
                'description' => 'Fabrication of metal structures and structural components.',
            ],
            [
                'title' => 'Manufacture of electronic components',
                'code' => '26110',
                'description' => 'Manufacture of electronic components such as semiconductors and circuit boards.',
            ],
            [
                'title' => 'Manufacture of batteries and accumulators',
                'code' => '27200',
                'description' => 'Manufacture of primary and secondary batteries and accumulators.',
            ],
            [
                'title' => 'Manufacture of engines and turbines, except aircraft, vehicle and cycle engines',
                'code' => '28110',
                'description' => 'Manufacture of engines and turbines for industrial and marine use.',
            ],
            [
                'title' => 'Manufacture of motor vehicles',
                'code' => '29100',
                'description' => 'Design and manufacture of cars and other motor vehicles.',
            ],
            [
                'title' => 'Manufacture of air and spacecraft and related machinery',
                'code' => '30300',
                'description' => 'Manufacture of aircraft, spacecraft and associated machinery.',
            ],
            [
                'title' => 'Manufacture of other furniture',
                'code' => '31090',
                'description' => 'Manufacture of furniture not classified elsewhere.',
            ],
            [
                'title' => 'Manufacture of sports goods',
                'code' => '32300',
                'description' => 'Manufacture of equipment and goods used for sports and outdoor activities.',
            ],
            [
                'title' => 'Repair of machinery',
                'code' => '33120',
                'description' => 'Repair and maintenance of industrial and general machinery.',
            ],
            [
                'title' => 'Production of electricity',
                'code' => '35110',
                'description' => 'Generation of electricity from any energy source.',
            ],
            [
                'title' => 'Water collection, treatment and supply',
                'code' => '36000',
                'description' => 'Collection, purification and distribution of water.',
            ],
            [
                'title' => 'Collection of non-hazardous waste',
                'code' => '38110',
                'description' => 'Collection of non-hazardous solid waste for disposal or recycling.',
            ],
            [
                'title' => 'Development of building projects',
                'code' => '41100',
                'description' => 'Assembly and management of building development projects.',
            ],
            [
                'title' => 'Construction of residential and non-residential buildings',
                'code' => '41200',
                'description' => 'General construction of residential and commercial buildings.',
            ],
            [
                'title' => 'Construction of roads and motorways',
                'code' => '42110',
                'description' => 'Construction and surfacing of roads and motorways.',
            ],
            [
                'title' => 'Electrical installation',
                'code' => '43210',
                'description' => 'Installation of electrical wiring and fittings in buildings.',
            ],
            [
                'title' => 'Plumbing, heat and air-conditioning installation',
                'code' => '43220',
                'description' => 'Installation of plumbing, heating and air-conditioning systems.',
            ],
            [
                'title' => 'Other building completion and finishing',
                'code' => '43390',
                'description' => 'Building completion and finishing work not classified elsewhere.',
            ],
            [
                'title' => 'Maintenance and repair of motor vehicles',
                'code' => '45200',
                'description' => 'Servicing, maintenance and repair of motor vehicles.',
            ],
            [
                'title' => 'Agents involved in the sale of a variety of goods',
                'code' => '46190',
                'description' => 'Wholesale agents dealing in a wide range of goods.',
            ],
            [
                'title' => 'Retail sale in non-specialised stores with food, beverages or tobacco predominating',
                'code' => '47110',
                'description' => 'General retail stores primarily selling food, beverages or tobacco.',
            ],
            [
                'title' => 'Retail sale via mail order houses or via Internet',
                'code' => '47910',
                'description' => 'Retail sale of goods through mail order or online channels.',
            ],
            [
                'title' => 'Freight transport by road',
                'code' => '49410',
                'description' => 'Transport of goods by road haulage.',
            ],
            [
                'title' => 'Warehousing and storage',
                'code' => '52100',
                'description' => 'Storage and warehousing of goods on behalf of third parties.',
            ],
            [
                'title' => 'Hotels and similar accommodation',
                'code' => '55100',
                'description' => 'Provision of short-stay accommodation in hotels and similar establishments.',
            ],
            [
                'title' => 'Licensed restaurants',
                'code' => '56101',
                'description' => 'Restaurants licensed to serve alcoholic beverages with meals.',
            ],
            [
                'title' => 'Book publishing',
                'code' => '58110',
                'description' => 'Publishing of books in print or electronic form.',
            ],
            [
                'title' => 'Motion picture production activities',
                'code' => '59111',
                'description' => 'Production of films, television programmes and video content.',
            ],
            [
                'title' => 'Other telecommunications activities',
                'code' => '61900',
                'description' => 'Telecommunications activities not classified elsewhere.',
            ],
            [
                'title' => 'Business and domestic software development',
                'code' => '62012',
                'description' => 'Development of bespoke software for business and domestic use.',
            ],
            [
                'title' => 'Information technology consultancy activities',
                'code' => '62020',
                'description' => 'Provision of IT consultancy and advisory services.',
            ],
            [
                'title' => 'Data processing, hosting and related activities',
                'code' => '63110',
                'description' => 'Data processing, web hosting and related infrastructure services.',
            ],
            [
                'title' => 'Banks',
                'code' => '64191',
                'description' => 'Banking institutions providing deposit and lending services.',
            ],
            [
                'title' => 'Other credit granting',
                'code' => '64920',
                'description' => 'Provision of credit not classified elsewhere, such as personal loans.',
            ],
            [
                'title' => 'Activities of insurance agents and brokers',
                'code' => '66220',
                'description' => 'Intermediary activities for the sale of insurance products.',
            ],
            [
                'title' => 'Buying and selling of own real estate',
                'code' => '68100',
                'description' => 'Purchase and sale of real estate held on own account.',
            ],
            [
                'title' => 'Other letting and operating of own or leased real estate',
                'code' => '68209',
                'description' => 'Letting and management of owned or leased real estate.',
            ],
            [
                'title' => 'Barristers at law',
                'code' => '69101',
                'description' => 'Legal advocacy and courtroom representation services.',
            ],
            [
                'title' => 'Solicitors',
                'code' => '69102',
                'description' => 'Provision of general legal advice and representation.',
            ],
            [
                'title' => 'Accounting and auditing activities',
                'code' => '69201',
                'description' => 'Preparation of accounts and statutory audit services.',
            ],
            [
                'title' => 'Activities of head offices',
                'code' => '70100',
                'description' => 'Administrative and management activities of head office functions.',
            ],
            [
                'title' => 'Financial management',
                'code' => '70221',
                'description' => 'Provision of financial management and advisory services.',
            ],
            [
                'title' => 'Management consultancy activities other than financial management',
                'code' => '70229',
                'description' => 'General management consultancy services excluding financial management.',
            ],
            [
                'title' => 'Engineering design activities for industrial process and production',
                'code' => '71121',
                'description' => 'Engineering design services for industrial processes and production lines.',
            ],
            [
                'title' => 'Other research and experimental development on natural sciences and engineering',
                'code' => '72190',
                'description' => 'Research and development activities in the natural sciences and engineering.',
            ],
            [
                'title' => 'Advertising agencies',
                'code' => '73110',
                'description' => 'Creation and placement of advertising campaigns on behalf of clients.',
            ],
            [
                'title' => 'Specialised design activities',
                'code' => '74100',
                'description' => 'Specialised design services such as graphic and industrial design.',
            ],
            [
                'title' => 'Portrait photographic activities',
                'code' => '74201',
                'description' => 'Photographic services specialising in portraiture.',
            ],
            [
                'title' => 'Human resources provision and management of human resources functions',
                'code' => '78300',
                'description' => 'Provision and management of outsourced human resources functions.',
            ],
            [
                'title' => 'Private security activities',
                'code' => '80100',
                'description' => 'Provision of private security guarding and protection services.',
            ],
            [
                'title' => 'General cleaning of buildings',
                'code' => '81210',
                'description' => 'General interior cleaning services for commercial and residential buildings.',
            ],
            [
                'title' => 'Other business support service activities not elsewhere classified',
                'code' => '82990',
                'description' => 'Business support services not classified elsewhere.',
            ],
            [
                'title' => 'Other education not elsewhere classified',
                'code' => '85590',
                'description' => 'Educational activities not classified elsewhere, such as training courses.',
            ],
            [
                'title' => 'General medical practice activities',
                'code' => '86210',
                'description' => 'Provision of outpatient medical consultation and treatment by general practitioners.',
            ],
            [
                'title' => 'Dental practice activities',
                'code' => '86230',
                'description' => 'Provision of dental treatment and oral healthcare services.',
            ],
            [
                'title' => 'Residential nursing care activities',
                'code' => '87100',
                'description' => 'Provision of residential nursing care for the elderly or infirm.',
            ],
            [
                'title' => 'Performing arts',
                'code' => '90010',
                'description' => 'Production and staging of live theatrical and performing arts.',
            ],
            [
                'title' => 'Operation of sports facilities',
                'code' => '93110',
                'description' => 'Operation and management of sports facilities and venues.',
            ],
            [
                'title' => 'Activities of other membership organisations not elsewhere classified',
                'code' => '94990',
                'description' => 'Membership organisation activities not classified elsewhere.',
            ],
            [
                'title' => 'Hairdressing and other beauty treatment',
                'code' => '96020',
                'description' => 'Hairdressing, barbering and other beauty treatment services.',
            ],
        ];
    }
}
