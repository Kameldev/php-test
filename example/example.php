<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Entity\Quote;
use App\Entity\Template;
use App\TemplateManager;


// Simple router pour serveur web
if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] === '/') {
    header('Content-Type: text/html; charset=utf-8');
    echo "<h1> Template Manager - Demo</h1>";
    echo "<pre>";
}

$faker = \Faker\Factory::create();

$template = new Template(
    1,
    'Votre livraison à [quote:destination_name]',
    "
Bonjour [user:first_name],

Merci de nous avoir contacté pour votre livraison à [quote:destination_name].

Détails: [quote:summary_html]
Lien: [quote:destination_link]

Bien cordialement,

L'équipe de Shipper
"
);

$templateManager = new TemplateManager();

$quote = new Quote(
    $faker->randomNumber(),
    $faker->randomNumber(),
    $faker->randomNumber(),
    new DateTime($faker->date())
);

echo "=== TEMPLATE ORIGINAL ===\n";
echo "Subject: " . $template->getSubject() . "\n";
echo "Content: " . $template->getContent() . "\n\n";

echo "=== APRÈS TRAITEMENT ===\n";
$message = $templateManager->getTemplateComputed($template, ['quote' => $quote]);

echo "Subject: " . $message->getSubject() . "\n";
echo "Content: " . $message->getContent() . "\n";

if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] === '/') {
    echo "</pre>";
    echo "<p><strong> Refactoring terminé.</strong></p>";
}