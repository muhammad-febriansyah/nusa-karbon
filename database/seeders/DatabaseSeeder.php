<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ProductCategorySeeder::class,
            ContainerTypeSeeder::class,
            PackagingOptionSeeder::class,
            ProductSeeder::class,
            CertificateSeeder::class,
            RepresentativeSeeder::class,
            CompanyContentSeeder::class,
            FaqSeeder::class,
            PageBlockSeeder::class,
            PostSeeder::class,
            SiteSettingSeeder::class,
            ThemeSeeder::class,
        ]);

        User::factory()
            ->create([
                'name' => 'Admin Nusacarbon',
                'email' => 'admin@nusacarbon.com',
            ])
            ->assignRole('admin');
    }
}
