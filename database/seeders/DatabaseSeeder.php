<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Listing;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- 1. Creăm Utilizatorii ---
        $eduard = User::create([
            'name' => 'Eduard-Valentin Apataschivei',
            'email' => 'eduard@master.usv',
            'password' => Hash::make('parola123'),
            'phone' => '0700112233',
            'city' => 'Suceava',
            'role' => 'admin'
        ]);

        $andrei = User::create([
            'name' => 'Andrei Popescu',
            'email' => 'andrei@example.com',
            'password' => Hash::make('parola123'),
            'phone' => '0711223344',
            'city' => 'Suceava',
            'role' => 'user'
        ]);

        $maria = User::create([
            'name' => 'Maria Ionescu',
            'email' => 'maria@example.com',
            'password' => Hash::make('parola123'),
            'phone' => '0799887766',
            'city' => 'Botoșani',
            'role' => 'user'
        ]);

        // --- 2. Creăm Categoriile ---
        $catIT = Category::create([
            'name' => 'IT & Programare',
            'description' => 'Servicii de dezvoltare web, scripturi și meditații IT.'
        ]);

        $catGaming = Category::create([
            'name' => 'Gaming & Game Dev',
            'description' => 'Asistență în jocuri, creare servere și dezvoltare 3D.'
        ]);

        $catGatit = Category::create([
            'name' => 'Gastronomie',
            'description' => 'Preparare mâncare la domiciliu și rețete personalizate.'
        ]);

        $catCuratenie = Category::create([
            'name' => 'Curățenie & Casă',
            'description' => 'Servicii de curățenie și reparații mărunte.'
        ]);

        // --- 3. Creăm Anunțurile ---
        
        // Anunțuri Eduard (IT & Game Dev)
        Listing::create([
            'provider_id' => $eduard->id,
            'category_id' => $catIT->id,
            'title' => 'Meditații Programare Java și JADE',
            'description' => 'Ofer explicații practice pentru programare orientată pe obiecte, concepte JADE, multithreading și lucrul cu colecții.',
            'price' => 80,
            'priceType' => 'pe oră',
            'city' => 'Suceava',
            'status' => 'activ'
        ]);

        Listing::create([
            'provider_id' => $eduard->id,
            'category_id' => $catIT->id,
            'title' => 'Dezvoltare aplicații web (Laravel & Next.js)',
            'description' => 'Construiesc platforme web full-stack folosind Next.js pe frontend și Laravel (sau Express/Python) pe backend. Integrare baze de date MySQL/PostgreSQL.',
            'price' => 1500,
            'priceType' => 'fix',
            'city' => 'Remote',
            'status' => 'activ'
        ]);

        // Anunțuri Andrei (Gaming)
        Listing::create([
            'provider_id' => $andrei->id,
            'category_id' => $catGaming->id,
            'title' => 'Scripturi C# și mecanici Unity 3D',
            'description' => 'Te ajut să implementezi un New Input System, controllere de cameră third-person/first-person și animații pentru jocuri low-poly în Unity.',
            'price' => 100,
            'priceType' => 'pe oră',
            'city' => 'Remote',
            'status' => 'activ'
        ]);

        Listing::create([
            'provider_id' => $andrei->id,
            'category_id' => $catGaming->id,
            'title' => 'Asistență modare și server SA-MP',
            'description' => 'Te ajut să îți configurezi propriul server de GTA San Andreas Multiplayer, să instalezi moduri de client și să rezolvi problemele tehnice.',
            'price' => 50,
            'priceType' => 'fix',
            'city' => 'Remote',
            'status' => 'activ'
        ]);

        Listing::create([
            'provider_id' => $andrei->id,
            'category_id' => $catGaming->id,
            'title' => 'Ghid farming Metin2 (Server 2009)',
            'description' => 'Consultanță pentru build-uri de Războinic Corp la nivel mic (32+), rute de farming optime și strategii pentru resetarea abilităților.',
            'price' => 20,
            'priceType' => 'fix',
            'city' => 'Remote',
            'status' => 'activ'
        ]);

        // Anunțuri Maria (Gastronomie & Casă)
        Listing::create([
            'provider_id' => $maria->id,
            'category_id' => $catGatit->id,
            'title' => 'Preparare deserturi: Iaurt Stracciatella de casă',
            'description' => 'Prepar la comandă iaurt stracciatella 100% natural și alte deserturi de casă proaspete.',
            'price' => 45,
            'priceType' => 'fix',
            'city' => 'Botoșani',
            'status' => 'activ'
        ]);

        Listing::create([
            'provider_id' => $maria->id,
            'category_id' => $catGatit->id,
            'title' => 'Meniu complet: Pui cu sos de usturoi și piure',
            'description' => 'Gătesc la domiciliul clientului mâncare tradițională. Specialitatea casei: pulpe de pui cu sos cremos de usturoi, piure și macaroane cu lapte.',
            'price' => 120,
            'priceType' => 'pe lucrare',
            'city' => 'Botoșani',
            'status' => 'activ'
        ]);

        Listing::create([
            'provider_id' => $maria->id,
            'category_id' => $catCuratenie->id,
            'title' => 'Servicii de curățenie generală',
            'description' => 'Ofer servicii de curățenie de întreținere pentru apartamente cu 2 și 3 camere.',
            'price' => 40,
            'priceType' => 'pe oră',
            'city' => 'Botoșani',
            'status' => 'activ'
        ]);
    }
}