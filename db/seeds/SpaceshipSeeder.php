<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class SpaceshipSeeder extends AbstractSeed
{
    public function run(): void
    {
        $faker = Faker\Factory::create();

        $data = [];
        for ($i = 0; $i < 10; $i++) {
            $data[] = [
                'id' => $faker->numberBetween(),
                'name' => $faker->name(),
                'description' => $faker->text( 255 ),
                'image' => $faker->imageUrl(640, 480),
                'category_id' => 1,
                'publisher_id' => 1,
            ];
        }

        $this->table('space_ship')->insert($data)->saveData();
    }
}
