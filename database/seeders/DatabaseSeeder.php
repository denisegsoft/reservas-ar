<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Property;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ProvinceSeeder::class);
        $this->call(CitySeeder::class);
        $this->call(PropertyTypeSeeder::class);

        Setting::set('avatar_required', '1');

        // Admin
        $admin = User::create([
            'name'      => 'Administrador',
            'last_name' => '',
            'email'     => 'admin@reservatuespacio.com',
            'password'  => Hash::make('password'),
            'role'      => 'admin',
            'phone'     => '1100000000',
            'avatar'    => 'https://api.dicebear.com/9.x/adventurer/svg?seed=Admin',
        ]);

        // Owner 1
        $owner1 = User::create([
            'name'      => 'Carlos',
            'last_name' => 'Mendez',
            'email'     => 'carlos@owner.com',
            'password'  => Hash::make('password'),
            'role'      => 'user',
            'phone'     => '1155555555',
            'avatar'    => 'https://api.dicebear.com/9.x/adventurer/svg?seed=Carlos',
        ]);

        // Owner 2
        $owner2 = User::create([
            'name'      => 'Laura',
            'last_name' => 'Gomez',
            'email'     => 'laura@owner.com',
            'password'  => Hash::make('password'),
            'role'      => 'user',
            'phone'     => '1166666666',
            'avatar'    => 'https://api.dicebear.com/9.x/adventurer/svg?seed=Laura',
        ]);

        // Client
        User::create([
            'name'      => 'Maria',
            'last_name' => 'Rodriguez',
            'email'     => 'maria@gmail.com',
            'password'  => Hash::make('password'),
            'role'      => 'user',
            'phone'     => '1177777777',
            'avatar'    => 'https://api.dicebear.com/9.x/adventurer/svg?seed=Maria',
        ]);

        // Propiedades
        $propiedades = [
            [
                'user_id' => $owner1->id,
                'name' => 'Quinta Los Pinos',
                'slug' => 'quinta-los-pinos',
                'description' => 'Hermosa quinta con pileta climatizada, quincho techado y amplio jardin. Ideal para eventos de todo tipo. Contamos con instalaciones de primera calidad y un entorno natural privilegiado rodeado de pinos centenarios.',
                'short_description' => 'Quinta de lujo con pileta climatizada y quincho techado en zona privada.',
                'address' => 'Ruta 2 Km 45',
                'city' => 'Canuelas',
                'state' => 'Buenos Aires',
                'price_per_day' => 85000,
                'price_weekend' => 110000,
                'capacity' => 80,
                'bedrooms' => 4,
                'bathrooms' => 3,
                'parking_spots' => 15,
                'amenities' => ['pileta', 'parrilla', 'quincho', 'wifi', 'estacionamiento', 'aire_acondicionado', 'salon_eventos'],
                'status' => 'active',
                'rating' => 4.80,
                'reviews_count' => 23,
                'featured' => true,
                'min_days' => 1,
                'rules' => ['No se permiten mascotas', 'Horario de ingreso: 10hs', 'Capacidad maxima estricta'],
            ],
            [
                'user_id' => $owner1->id,
                'name' => 'Salon El Rancho',
                'slug' => 'salon-el-rancho',
                'description' => 'Autentico salon con todo el encanto rural. Perfecto para asados en familia, cumpleanos y reuniones de amigos. Amplia galeria, fogon y cancha de futbol 5 incluida.',
                'short_description' => 'Autentica experiencia con cancha de futbol y fogon.',
                'address' => 'Calle Las Acacias 1250',
                'city' => 'Ezeiza',
                'state' => 'Buenos Aires',
                'price_per_day' => 55000,
                'price_weekend' => 75000,
                'capacity' => 50,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'parking_spots' => 10,
                'amenities' => ['parrilla', 'fogon', 'cancha_futbol', 'estacionamiento', 'wifi', 'juegos_ninos'],
                'status' => 'active',
                'rating' => 4.60,
                'reviews_count' => 15,
                'featured' => true,
                'min_days' => 1,
                'rules' => ['Sin musica despues de las 24hs', 'Dejar el espacio como se encontro'],
            ],
            [
                'user_id' => $owner2->id,
                'name' => 'Villa Serena Luxury',
                'slug' => 'villa-serena-luxury',
                'description' => 'La experiencia mas lujosa en reserva de propiedades. Jacuzzi privado, salon de eventos completamente equipado, cocina gourmet y servicio de mozos incluido. Para eventos que merecen lo mejor.',
                'short_description' => 'La experiencia mas exclusiva con jacuzzi, salon y servicio completo.',
                'address' => 'Countries del Sol, Lote 23',
                'city' => 'Pilar',
                'state' => 'Buenos Aires',
                'price_per_day' => 150000,
                'price_weekend' => 200000,
                'capacity' => 120,
                'bedrooms' => 6,
                'bathrooms' => 4,
                'parking_spots' => 25,
                'amenities' => ['pileta', 'jacuzzi', 'parrilla', 'quincho', 'salon_eventos', 'wifi', 'estacionamiento', 'seguridad', 'sonido', 'cocina_equipada'],
                'status' => 'active',
                'rating' => 4.95,
                'reviews_count' => 41,
                'featured' => true,
                'min_days' => 1,
                'rules' => ['Evento con contrato previo requerido', 'No se admiten menores sin adultos responsables'],
            ],
            [
                'user_id' => $owner2->id,
                'name' => 'Quinta Don Pedro',
                'slug' => 'quinta-don-pedro',
                'description' => 'Quinta tradicional con mucho espacio verde. Perfecta para eventos de empresa, retiros y celebraciones familiares. Amplia pileta, cancha de tenis y estacionamiento para 30 autos.',
                'short_description' => 'Clasica quinta con tenis, pileta y grandes espacios verdes.',
                'address' => 'Av. de los Ceibos 450',
                'city' => 'Tigre',
                'state' => 'Buenos Aires',
                'price_per_day' => 70000,
                'price_weekend' => 95000,
                'capacity' => 100,
                'bedrooms' => 5,
                'bathrooms' => 4,
                'parking_spots' => 30,
                'amenities' => ['pileta', 'cancha_tenis', 'parrilla', 'quincho', 'estacionamiento', 'wifi', 'tv_smart', 'lavarropas'],
                'status' => 'active',
                'rating' => 4.70,
                'reviews_count' => 28,
                'featured' => false,
                'min_days' => 1,
            ],
        ];

        foreach ($propiedades as $propiedadData) {
            Property::create($propiedadData);
        }

        $this->command->info('Datos de ejemplo creados exitosamente!');
        $this->command->info('Admin: admin@reservatuespacio.com / password');
        $this->command->info('Propietario: carlos@owner.com / password');
        $this->command->info('Cliente: maria@gmail.com / password');
    }
}
