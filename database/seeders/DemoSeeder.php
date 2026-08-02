<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Entry;
use App\Models\Profile;
use App\Models\Purchase;
use App\Models\Question;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Verification;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $categories = $this->seedCategories();
            $demo       = $this->seedDemoUser();
            $sellers    = $this->seedSellers();

            $feeRate = (float) config('datacore.platform_fee_rate', 0.05);

            $datasets = [
                [
                    'owner'           => $demo,
                    'title'           => 'Coffee Habits Survey 2026',
                    'cat'             => 'lifestyle',
                    'price'           => 45000,
                    'trending'        => true,
                    'clean'           => 'clean2',
                    'quality'         => 0.952,
                    'entries'         => 8,
                    'reviews'         => 3,
                    'metadata_fields' => [],
                    'questions'       => [
                        ['Full Name', 'text'],
                        ['Age', 'number'],
                        ['City', 'text'],
                        ['Cups Per Day', 'number'],
                        ['Preferred Brew', 'choice', ['Espresso', 'Latte', 'Drip', 'Cold Brew']],
                    ],
                ],
                [
                    'owner'           => $demo,
                    'title'           => 'E-Commerce UX Research',
                    'cat'             => 'lifestyle',
                    'status'          => 'draft',
                    'price'           => 0,
                    'reward'          => 3000,
                    'target'          => 60,
                    'trending'        => false,
                    'clean'           => 'raw',
                    'quality'         => null,
                    'entries'         => 0,
                    'reviews'         => 0,
                    'metadata_fields' => ['age', 'city'],
                    'questions'       => [
                        ['How often do you shop online?', 'choice', ['Daily', 'Weekly', 'Monthly', 'Rarely']],
                        ['Which platform do you use most?', 'text'],
                        ['Biggest friction at checkout', 'paragraph'],
                    ],
                ],
                [
                    'owner'           => $demo,
                    'title'           => 'Remote Work Pulse 2026',
                    'cat'             => 'lifestyle',
                    'status'          => 'ongoing',
                    'price'           => 0,
                    'reward'          => 5000,
                    'target'          => 50,
                    'trending'        => false,
                    'clean'           => 'raw',
                    'quality'         => null,
                    'entries'         => 3,
                    'reviews'         => 0,
                    'metadata_fields' => ['age', 'profession', 'city'],
                    'questions'       => [
                        ['Days Remote Per Week', 'number'],
                        ['Role', 'text'],
                        ['Work Satisfaction', 'choice', ['Low', 'Medium', 'High']],
                        ['Biggest Challenge', 'paragraph'],
                    ],
                ],
                [
                    'owner'           => $sellers[0],
                    'title'           => 'Food Delivery Experience',
                    'cat'             => 'food',
                    'status'          => 'ongoing',
                    'price'           => 0,
                    'reward'          => 3000,
                    'target'          => 30,
                    'trending'        => false,
                    'clean'           => 'raw',
                    'quality'         => null,
                    'entries'         => 2,
                    'reviews'         => 0,
                    'metadata_fields' => ['age', 'city'],
                    'questions'       => [
                        ['App Used', 'choice', ['GoFood', 'GrabFood', 'ShopeeFood', 'Other']],
                        ['Orders Per Month', 'number'],
                        ['Average Spend', 'number'],
                        ['Satisfaction', 'choice', ['1', '2', '3', '4', '5']],
                        ['What Could Improve', 'paragraph'],
                    ],
                ],
                [
                    'owner'           => $sellers[1],
                    'title'           => 'Student Side Hustle Tracker',
                    'cat'             => 'finance',
                    'status'          => 'ongoing',
                    'price'           => 0,
                    'reward'          => 2000,
                    'target'          => 40,
                    'trending'        => false,
                    'clean'           => 'raw',
                    'quality'         => null,
                    'entries'         => 4,
                    'reviews'         => 0,
                    'metadata_fields' => ['age', 'profession', 'marital_status'],
                    'questions'       => [
                        ['Study Field', 'text'],
                        ['Has Side Hustle', 'choice', ['Yes', 'No']],
                        ['Type of Side Hustle', 'text'],
                        ['Monthly Income', 'number'],
                        ['Hours Per Week', 'number'],
                    ],
                ],
                [
                    'owner'           => $sellers[2],
                    'title'           => 'Mental Health at Work 2026',
                    'cat'             => 'health',
                    'status'          => 'ongoing',
                    'price'           => 0,
                    'reward'          => 4000,
                    'target'          => 25,
                    'trending'        => false,
                    'clean'           => 'raw',
                    'quality'         => null,
                    'entries'         => 1,
                    'reviews'         => 0,
                    'metadata_fields' => ['age', 'gender', 'marital_status'],
                    'questions'       => [
                        ['Age Group', 'choice', ['18-24', '25-34', '35-44', '45+']],
                        ['Industry', 'text'],
                        ['Stress Level', 'choice', ['Low', 'Moderate', 'High', 'Very High']],
                        ['Company Support', 'choice', ['None', 'Some', 'Good', 'Excellent']],
                        ['Open Feedback', 'paragraph'],
                    ],
                ],
                [
                    'owner'           => $sellers[0],
                    'title'           => 'Student Spending Q1',
                    'cat'             => 'finance',
                    'price'           => 60000,
                    'trending'        => true,
                    'clean'           => 'clean2',
                    'quality'         => 0.918,
                    'entries'         => 10,
                    'reviews'         => 4,
                    'metadata_fields' => [],
                    'questions'       => [
                        ['Age', 'number'],
                        ['Monthly Budget', 'number'],
                        ['Top Category', 'choice', ['Food', 'Transport', 'Rent', 'Leisure']],
                        ['Saves Monthly', 'choice', ['Yes', 'No']],
                    ],
                ],
                [
                    'owner'           => $sellers[1],
                    'title'           => 'Jakarta Commute Patterns',
                    'cat'             => 'mobility',
                    'price'           => 75000,
                    'trending'        => true,
                    'clean'           => 'clean2',
                    'quality'         => 0.889,
                    'entries'         => 12,
                    'reviews'         => 3,
                    'metadata_fields' => [],
                    'questions'       => [
                        ['Home Area', 'text'],
                        ['Commute Minutes', 'number'],
                        ['Main Mode', 'choice', ['Car', 'Motorbike', 'TransJakarta', 'MRT', 'Online Ride']],
                        ['Monthly Cost', 'number'],
                    ],
                ],
                [
                    'owner'           => $sellers[2],
                    'title'           => 'Campus Food Ratings',
                    'cat'             => 'food',
                    'price'           => 30000,
                    'trending'        => false,
                    'clean'           => 'clean1',
                    'quality'         => 0.842,
                    'entries'         => 9,
                    'reviews'         => 2,
                    'metadata_fields' => [],
                    'questions'       => [
                        ['Stall Name', 'text'],
                        ['Price Range', 'choice', ['Cheap', 'Medium', 'Pricey']],
                        ['Taste Score', 'number'],
                        ['Would Return', 'choice', ['Yes', 'No']],
                    ],
                ],
                [
                    'owner'           => $sellers[0],
                    'title'           => 'Personal Finance Habits',
                    'cat'             => 'finance',
                    'price'           => 0,
                    'trending'        => false,
                    'clean'           => 'clean1',
                    'quality'         => 0.873,
                    'entries'         => 7,
                    'reviews'         => 1,
                    'metadata_fields' => [],
                    'questions'       => [
                        ['Age', 'number'],
                        ['Has Emergency Fund', 'choice', ['Yes', 'No']],
                        ['Investing', 'choice', ['Yes', 'No']],
                        ['Monthly Savings', 'number'],
                    ],
                ],
                [
                    'owner'           => $sellers[1],
                    'title'           => 'Gym Routine Tracker',
                    'cat'             => 'health',
                    'price'           => 40000,
                    'trending'        => true,
                    'clean'           => 'clean2',
                    'quality'         => 0.901,
                    'entries'         => 11,
                    'reviews'         => 2,
                    'metadata_fields' => [],
                    'questions'       => [
                        ['Age', 'number'],
                        ['Sessions Per Week', 'number'],
                        ['Goal', 'choice', ['Strength', 'Cardio', 'Weight Loss', 'Mobility']],
                        ['Uses Tracker', 'choice', ['Yes', 'No']],
                    ],
                ],
                [
                    'owner'           => $sellers[2],
                    'title'           => 'Online Course Feedback',
                    'cat'             => 'education',
                    'price'           => 35000,
                    'trending'        => false,
                    'clean'           => 'clean2',
                    'quality'         => 0.865,
                    'entries'         => 8,
                    'reviews'         => 2,
                    'metadata_fields' => [],
                    'questions'       => [
                        ['Age', 'number'],
                        ['Completed Course', 'choice', ['Yes', 'No']],
                        ['Rating', 'number'],
                        ['Topic', 'text'],
                    ],
                ],
            ];

            $created = [];

            foreach ($datasets as $d) {
                $created[] = $this->seedDataset($d, $categories, $feeRate);
            }

            $regular = $this->seedRegularUser();
            $this->seedDemoWallet($demo);
            $this->seedDemoPurchase($demo, $created[2]);
            $this->seedRegularWallet($regular);
        });
    }

    private function seedCategories(): array
    {
        $items = [
            ['name' => 'Lifestyle',  'slug' => 'lifestyle',  'icon' => 'coffee'],
            ['name' => 'Finance',    'slug' => 'finance',    'icon' => 'wallet'],
            ['name' => 'Mobility',   'slug' => 'mobility',   'icon' => 'bus'],
            ['name' => 'Education',  'slug' => 'education',  'icon' => 'graduation-cap'],
            ['name' => 'Health',     'slug' => 'health',     'icon' => 'heart-pulse'],
            ['name' => 'Food',       'slug' => 'food',       'icon' => 'utensils'],
        ];

        $map = [];

        foreach ($items as $item) {
            $map[$item['slug']] = Category::firstOrCreate(['slug' => $item['slug']], $item);
        }

        return $map;
    }

    private function seedDemoUser(): User
    {
        $user = User::create([
            'name'              => 'Rangga Aditya',
            'email'             => 'demo@datacore.test',
            'password'          => 'password',
            'email_verified_at' => now(),
            'is_admin'          => true,
        ]);

        Profile::create([
            'user_id'        => $user->id,
            'phone_number'   => '+62 812 3456 7890',
            'gender'         => 'Male',
            'dob'            => '1995-01-25',
            'address'        => 'Jl. Sudirman No. 1, Jakarta',
            'city'           => 'Jakarta',
            'profession'     => 'Data Scientist',
            'marital_status' => 'Single',
        ]);

        Wallet::create(['user_id' => $user->id, 'balance' => 500000]);
        Verification::create(['user_id' => $user->id, 'id_number' => '3174012501990001', 'status' => 'verified']);

        Activity::create(['user_id' => $user->id, 'type' => 'system',   'title' => 'Welcome to DataCore',   'description' => 'Your account is ready.',                                 'is_read' => true]);
        Activity::create(['user_id' => $user->id, 'type' => 'cleaning', 'title' => 'Clean 2 complete',      'description' => 'Coffee Habits Survey 2026 — 8 rows fully refined.',     'is_read' => false]);
        Activity::create(['user_id' => $user->id, 'type' => 'reward',   'title' => 'Reward pool escrowed',  'description' => 'Rp 262,500 held for Remote Work Pulse 2026.',            'is_read' => false]);

        return $user;
    }

    private function seedSellers(): array
    {
        $people = [
            [
                'name'           => 'Maya Putri',
                'email'          => 'maya@datacore.test',
                'dob'            => '1998-03-12',
                'gender'         => 'Female',
                'city'           => 'Bandung',
                'profession'     => 'UI/UX Designer',
                'marital_status' => 'Single',
                'address'        => 'Jl. Dago No. 45, Bandung',
            ],
            [
                'name'           => 'Bagus Pratama',
                'email'          => 'bagus@datacore.test',
                'dob'            => '1993-07-22',
                'gender'         => 'Male',
                'city'           => 'Surabaya',
                'profession'     => 'Software Engineer',
                'marital_status' => 'Married',
                'address'        => 'Jl. Raya Darmo No. 12, Surabaya',
            ],
            [
                'name'           => 'Sari Wulandari',
                'email'          => 'sari@datacore.test',
                'dob'            => '1991-11-05',
                'gender'         => 'Female',
                'city'           => 'Yogyakarta',
                'profession'     => 'Marketing Manager',
                'marital_status' => 'Married',
                'address'        => 'Jl. Malioboro No. 88, Yogyakarta',
            ],
        ];

        $sellers = [];

        foreach ($people as $p) {
            $user = User::create([
                'name'              => $p['name'],
                'email'             => $p['email'],
                'password'          => 'password',
                'email_verified_at' => now(),
            ]);

            Profile::create([
                'user_id'        => $user->id,
                'gender'         => $p['gender'],
                'dob'            => $p['dob'],
                'address'        => $p['address'],
                'city'           => $p['city'],
                'profession'     => $p['profession'],
                'marital_status' => $p['marital_status'],
            ]);

            Wallet::create(['user_id' => $user->id, 'balance' => random_int(300000, 600000)]);
            Verification::create(['user_id' => $user->id, 'status' => 'verified']);

            $sellers[] = $user;
        }

        return $sellers;
    }

    private function seedDataset(array $d, array $categories, float $feeRate): Collection
    {
        $owner          = $d['owner'];
        $reward         = (int) ($d['reward'] ?? 0);
        $target         = $d['target'] ?? null;
        $metadataFields = $d['metadata_fields'] ?? [];
        $status         = $d['status'] ?? 'published';
        $budget         = ($status === 'ongoing' && $reward > 0 && $target > 0) ? (int) round($reward * $target * (1 + $feeRate)) : 0;

        $collection = Collection::create([
            'user_id'           => $owner->id,
            'category_id'       => $categories[$d['cat']]->id,
            'title'             => $d['title'],
            'description'       => 'A community-contributed dataset about ' . Str::lower($d['title']) . ', refined through the DataCore pipeline.',
            'type'              => 'survey',
            'price'             => $d['price'],
            'status'            => $d['status'] ?? 'published',
            'reward'            => $reward,
            'respondent_target' => $target,
            'reward_budget'     => $budget,
            'metadata_fields'   => $metadataFields,
            'quality_score'     => $d['quality'],
            'quality_avg'       => $d['quality'],
            'cleaning_report'   => $d['quality'] ? ['quality' => ['final_quality_score' => $d['quality'], 'avg_score' => $d['quality'], 'row_count' => $d['entries']]] : null,
        ]);

        $questions = [];

        foreach ($d['questions'] as $i => $q) {
            $questions[] = Question::create([
                'collection_id' => $collection->id,
                'title'         => $q[0],
                'type'          => $q[1],
                'options'       => ($q[1] === 'choice') ? ($q[2] ?? []) : null,
                'position'      => $i,
                'required'      => false,
            ]);
        }

        for ($i = 0; $i < $d['entries']; $i++) {
            [$raw, $clean1, $clean2] = $this->makeEntry($questions, $i);

            Entry::create([
                'collection_id' => $collection->id,
                'user_id'       => null,
                'raw_data'      => $raw,
                'clean1_data'   => in_array($d['clean'], ['clean1', 'clean2'], true) ? $clean1 : null,
                'clean2_data'   => $d['clean'] === 'clean2' ? $clean2 : null,
                'metadata'      => $this->makeEntryMetadata($metadataFields, $i),
                'status'        => $d['clean'],
            ]);
        }

        $this->seedReviews($collection, $d['reviews']);

        return $collection;
    }

    private function makeEntry(array $questions, int $i): array
    {
        $cities  = ['Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta', 'Medan', 'Semarang'];
        $names   = ['Andi', 'Bunga', 'Citra', 'Dimas', 'Eka', 'Fajar', 'Gita', 'Hadi'];
        $domains = ['mail.com', 'inbox.id', 'webmail.co'];

        $raw    = [];
        $clean1 = [];
        $clean2 = [];

        foreach ($questions as $q) {
            $key   = $q->title;
            $norm  = Str::lower($key);
            $isPii = Str::contains($norm, ['name', 'email', 'phone']);

            if (Str::contains($norm, 'email')) {
                $value = Str::lower($names[$i % count($names)]) . $i . '@' . $domains[$i % count($domains)];
            } elseif (Str::contains($norm, 'name')) {
                $value = $names[$i % count($names)] . ' ' . chr(65 + ($i % 26)) . '.';
            } elseif (Str::contains($norm, 'city') || Str::contains($norm, 'area')) {
                $value = $cities[$i % count($cities)];
            } elseif ($q->type === 'choice' && ! empty($q->options)) {
                $value = $q->options[$i % count($q->options)];
            } elseif ($q->type === 'number') {
                $value = (string) (10 + (($i * 7) % 50));
            } else {
                $value = 'Sample ' . ($i + 1);
            }

            $rawValue = ($i % 3 === 0 && $q->type !== 'number') ? '  ' . $value . ' ' : $value;
            $raw[$key] = $rawValue;

            if (! $isPii) {
                $clean1[$key] = $rawValue;
                $clean2[$key] = $q->type === 'number' ? (int) $value : trim($value);
            }
        }

        $clean1['QualityScore'] = round(0.8 + (($i % 5) * 0.04), 2);
        $clean2['QualityScore'] = round(0.88 + (($i % 4) * 0.03), 2);

        return [$raw, $clean1, $clean2];
    }

    private function makeEntryMetadata(array $requested, int $i): array
    {
        if (! $requested) {
            return [];
        }

        $ages     = [22, 26, 31, 28, 35, 24, 29, 33, 27, 30];
        $genders  = ['Male', 'Female', 'Female', 'Male', 'Female', 'Male'];
        $cities   = ['Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta', 'Medan', 'Semarang', 'Malang', 'Depok'];
        $professions    = ['Software Engineer', 'Marketing Manager', 'Designer', 'Teacher', 'Data Analyst', 'Freelancer', 'Nurse', 'Accountant'];
        $maritalStatuses = ['Single', 'Married', 'Single', 'Single', 'Married', 'Single', 'Married', 'Single'];

        $all = [
            'age'            => $ages[$i % count($ages)],
            'gender'         => $genders[$i % count($genders)],
            'city'           => $cities[$i % count($cities)],
            'profession'     => $professions[$i % count($professions)],
            'marital_status' => $maritalStatuses[$i % count($maritalStatuses)],
        ];

        return collect($all)->only($requested)->all();
    }

    private function seedReviews(Collection $collection, int $count): void
    {
        if ($count === 0) {
            return;
        }

        $comments = [
            'Clean, well-structured, exactly what I needed.',
            'Great quality after Clean 2 — saved me hours.',
            'Solid sample size and the columns are consistent.',
            'Useful dataset, would buy from this seller again.',
            'Good value. The refinement really shows.',
        ];

        // Reviewers seeded separately to avoid circular dependency
    }

    private function seedDemoWallet(User $demo): void
    {
        Transaction::create([
            'user_id'     => $demo->id,
            'reference'   => 'TXN-' . strtoupper(Str::random(8)),
            'type'        => 'topup',
            'direction'   => 'credit',
            'amount'      => 600000,
            'status'      => 'success',
            'description' => 'Top-up via Virtual Account',
            'created_at'  => now()->subDays(10),
        ]);

        Transaction::create([
            'user_id'     => $demo->id,
            'reference'   => 'TXN-' . strtoupper(Str::random(8)),
            'type'        => 'payment',
            'direction'   => 'debit',
            'amount'      => 25000,
            'status'      => 'success',
            'description' => 'Clean 2 premium refine — Coffee Habits Survey 2026',
            'created_at'  => now()->subDays(7),
        ]);

        Transaction::create([
            'user_id'     => $demo->id,
            'reference'   => 'TXN-' . strtoupper(Str::random(8)),
            'type'        => 'escrow',
            'direction'   => 'debit',
            'amount'      => (int) round(5000 * 50 * 1.05),
            'status'      => 'success',
            'description' => 'Survey reward pool — Remote Work Pulse 2026',
            'created_at'  => now()->subDays(3),
        ]);
    }

    private function seedDemoPurchase(User $demo, Collection $collection): void
    {
        Purchase::create(['collection_id' => $collection->id, 'user_id' => $demo->id, 'amount' => $collection->price]);

        Transaction::create([
            'user_id'       => $demo->id,
            'collection_id' => $collection->id,
            'reference'     => 'TXN-' . strtoupper(Str::random(8)),
            'type'          => 'payment',
            'direction'     => 'debit',
            'amount'        => $collection->price,
            'status'        => 'success',
            'description'   => 'Purchased ' . $collection->title,
            'created_at'    => now()->subDays(2),
        ]);

        Activity::create(['user_id' => $demo->id, 'type' => 'purchase', 'title' => 'Dataset purchased', 'description' => 'You purchased ' . $collection->title . '.', 'is_read' => false]);
    }

    private function seedRegularUser(): User
    {
        $user = User::create([
            'name'              => 'Aura Kirana',
            'email'             => 'user@datacore.test',
            'password'          => 'password',
            'email_verified_at' => now(),
        ]);

        Profile::create([
            'user_id'        => $user->id,
            'phone_number'   => '+62 857 9812 3344',
            'gender'         => 'Female',
            'dob'            => '1999-04-17',
            'address'        => 'Jl. Kemang Raya No. 21, Jakarta Selatan',
            'city'           => 'Jakarta',
            'profession'     => 'Content Creator',
            'marital_status' => 'Single',
        ]);

        Wallet::create(['user_id' => $user->id, 'balance' => 175000]);

        Activity::create(['user_id' => $user->id, 'type' => 'system',  'title' => 'Welcome to DataCore', 'description' => 'Your account is ready. Verify your identity to start contributing.', 'is_read' => true]);
        Activity::create(['user_id' => $user->id, 'type' => 'reward',  'title' => 'Survey reward received', 'description' => 'Rp 5,000 earned for completing Remote Work Pulse 2026.', 'is_read' => false]);

        return $user;
    }

    private function seedRegularWallet(User $user): void
    {
        Transaction::create([
            'user_id'     => $user->id,
            'reference'   => 'TXN-' . strtoupper(Str::random(8)),
            'type'        => 'topup',
            'direction'   => 'credit',
            'amount'      => 150000,
            'status'      => 'success',
            'description' => 'Top-up via Virtual Account',
            'created_at'  => now()->subDays(5),
        ]);

        Transaction::create([
            'user_id'     => $user->id,
            'reference'   => 'TXN-' . strtoupper(Str::random(8)),
            'type'        => 'reward',
            'direction'   => 'credit',
            'amount'      => 5000,
            'status'      => 'success',
            'description' => 'Survey reward — Remote Work Pulse 2026',
            'created_at'  => now()->subDays(2),
        ]);

        Transaction::create([
            'user_id'     => $user->id,
            'reference'   => 'TXN-' . strtoupper(Str::random(8)),
            'type'        => 'reward',
            'direction'   => 'credit',
            'amount'      => 20000,
            'status'      => 'success',
            'description' => 'Survey reward — Food Delivery Experience 2026',
            'created_at'  => now()->subDays(1),
        ]);
    }
}
