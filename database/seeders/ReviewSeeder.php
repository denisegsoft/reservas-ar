<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviewers = [
            ['name' => 'Gonzalo',  'last_name' => 'Fernández', 'email' => 'gonzalo@gmail.com'],
            ['name' => 'Valentina','last_name' => 'López',     'email' => 'valentina@gmail.com'],
            ['name' => 'Martín',   'last_name' => 'Sosa',      'email' => 'martin@gmail.com'],
            ['name' => 'Luciana',  'last_name' => 'Herrera',   'email' => 'luciana@gmail.com'],
            ['name' => 'Diego',    'last_name' => 'Romero',    'email' => 'diego@gmail.com'],
            ['name' => 'Carolina', 'last_name' => 'Paz',       'email' => 'carolina@gmail.com'],
            ['name' => 'Matías',   'last_name' => 'Álvarez',   'email' => 'matias@gmail.com'],
            ['name' => 'Florencia','last_name' => 'Méndez',    'email' => 'florencia@gmail.com'],
        ];

        $users = [];
        foreach ($reviewers as $data) {
            $users[] = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'      => $data['name'],
                    'last_name' => $data['last_name'],
                    'password'  => Hash::make('password'),
                    'role'      => 'user',
                    'phone'     => '1199999999',
                    'avatar'    => 'https://api.dicebear.com/9.x/adventurer/svg?seed=' . urlencode($data['name']),
                ]
            );
        }

        $reviews = [
            // property slug => [rating, comment, user_index]
            'quinta-los-pinos' => [
                [5, 'Excelente propiedad, todo estaba impecable. La pileta climatizada es increíble y el quincho enorme. El propietario muy atento. Volvería sin dudar.', 0],
                [5, 'Festejamos el cumple de mi mamá y quedó todo espectacular. El lugar es hermoso, muy limpio y con todas las comodidades. 100% recomendable.', 1],
                [4, 'Muy linda quinta, amplia y bien equipada. Solo faltó un poco más de iluminación exterior pero en general todo perfecto.', 2],
                [5, 'La mejor experiencia que tuvimos reservando una quinta. El entorno natural es increíble y la atención del propietario excelente.', 3],
            ],
            'salon-el-rancho' => [
                [5, 'Organizamos un asado familiar y fue todo un éxito. La cancha de fútbol 5 fue el plus que necesitábamos. Muy buen precio y excelente atención.', 4],
                [4, 'Lindo lugar, auténtico y con mucho espacio. El fogón fue un toque especial. Lo recomendaría para reuniones informales.', 5],
                [5, 'Perfecto para nuestro evento de empresa. Amplio, cómodo y con estacionamiento suficiente. El propietario muy predispuesto.', 6],
            ],
            'villa-serena-luxury' => [
                [5, 'Una experiencia de lujo total. El jacuzzi, el salón de eventos y el servicio de mozos superaron todas nuestras expectativas. Para un evento especial, sin dudas.', 0],
                [5, 'Celebramos nuestro casamiento aquí y fue perfecto. Cada detalle cuidado, el personal increíble y el lugar de ensueño. ¡Gracias!', 7],
                [5, 'No encontramos palabras para describir la calidad. Todo premium, todo impecable. Vale cada peso invertido.', 1],
                [4, 'Excelente propiedad, muy lujosa. El único detalle es que el acceso es un poco complicado pero una vez adentro todo es perfecto.', 2],
            ],
            'quinta-don-pedro' => [
                [5, 'Quinta enorme con mucho espacio verde. La cancha de tenis fue un plus inesperado. Los chicos disfrutaron muchísimo de la pileta.', 3],
                [4, 'Muy buena relación precio-calidad. El lugar es amplio y bien mantenido. Para una reunión familiar grande es ideal.', 4],
                [5, 'Perfecto para nuestro retiro de empresa. Mucha privacidad, buen equipamiento y el propietario muy atento a cada necesidad.', 5],
            ],
        ];

        $total = 0;
        foreach ($reviews as $slug => $propReviews) {
            $property = Property::where('slug', $slug)->first();
            if (!$property) continue;

            foreach ($propReviews as [$rating, $comment, $userIdx]) {
                $user = $users[$userIdx];

                // Crear reserva completada
                $checkIn  = now()->subMonths(rand(1, 6))->subDays(rand(1, 20));
                $checkOut = $checkIn->copy()->addDays(rand(1, 3));
                $days     = $checkIn->diffInDays($checkOut);
                $subtotal = $property->price_per_day * $days;
                $fee      = round($subtotal * 0.05);

                $reservation = Reservation::create([
                    'property_id'    => $property->id,
                    'user_id'        => $user->id,
                    'check_in'       => $checkIn->toDateString(),
                    'check_out'      => $checkOut->toDateString(),
                    'guests'         => rand(10, 40),
                    'price_per_day'  => $property->price_per_day,
                    'total_days'     => max(1, $days),
                    'subtotal'       => $subtotal,
                    'service_fee'    => $fee,
                    'total_amount'   => $subtotal + $fee,
                    'status'         => 'completed',
                    'payment_status' => 'paid',
                ]);

                Review::create([
                    'property_id'    => $property->id,
                    'user_id'        => $user->id,
                    'reservation_id' => $reservation->id,
                    'rating'         => $rating,
                    'comment'        => $comment,
                    'approved'       => true,
                ]);

                $total++;
            }
        }

        $this->command->info("Reseñas creadas: {$total}");
    }
}
