<?php
require 'config.php';

$breads = [
    [
        'name' => 'Sourdough',
        'description' => 'Classic sourdough with a crisp crust and tangy flavor',
        'price' => 6.50,
        'image' => 'https://images.unsplash.com/photo-1595535873420-a599195b3f4a?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'
    ],
    [
        'name' => 'Baguette',
        'description' => 'Traditional French baguette with a golden crust',
        'price' => 4.50,
        'image' => 'https://images.unsplash.com/photo-1608190003443-86a6a84b2f3a?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'
    ],
    [
        'name' => 'Whole Wheat',
        'description' => 'Nutritious whole wheat bread with hearty texture',
        'price' => 5.75,
        'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'
    ],
    [
        'name' => 'Ciabatta',
        'description' => 'Italian ciabatta with large air pockets and chewy texture',
        'price' => 5.25,
        'image' => 'https://images.unsplash.com/photo-1608190003443-86a6a84b2f3a?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'
    ],
    [
        'name' => 'Rye Bread',
        'description' => 'Dark rye bread with rich, earthy flavor',
        'price' => 6.00,
        'image' => 'https://images.unsplash.com/photo-1517686469429-8bdb88b9f907?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'
    ],
    [
        'name' => 'Brioche',
        'description' => 'Rich and buttery French brioche',
        'price' => 7.50,
        'image' => 'https://images.unsplash.com/photo-1569925451506-8e9e1c9b0ee0?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'
    ]
];

$stmt = $pdo->prepare("INSERT INTO breads (name, description, price, image) VALUES (?, ?, ?, ?)");

foreach ($breads as $bread) {
    $stmt->execute([
        $bread['name'],
        $bread['description'],
        $bread['price'],
        $bread['image'],
        10 // Default stock value
    ]);
}

echo "Database populated successfully with " . count($breads) . " bread items!";
?>