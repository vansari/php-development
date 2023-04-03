<?php

$project = basename(realpath('.'));
$vendor = readline('What is the name of the vendor: ');
if (empty(trim($vendor))) {
    exit('A project needs a vendor name!');
}
$vendor = strtolower(preg_replace('/\s+/', '-', $vendor));
$customProject = readline("Do you want to change the project name? (Default: $project) [y|N]");
if ('y' === strtolower($customProject)) {
    $tmpProject = readline('Please enter your project name (Example: my-project): ');
    if (!preg_match('/^[a-z]+[a-z-]+[a-z$]/', $tmpProject)) {
        exit('Wrong project name pattern. Only lowercase and dashes are supported.');
    }
    $project = $tmpProject;
}
$description = readline('Describe your project in few words: ');

echo "Vendor is: $vendor" . PHP_EOL;
echo "Project is: $project" . PHP_EOL;
echo "Composer Name is: $vendor/$project" . PHP_EOL;
echo "Description is: $description" . PHP_EOL;

$replaces = [
    '{{ vendor }}' => $vendor,
    '{{ project }}' => $project,
    '{{ description }}' => $description,
];

function replaceCustoms($target, array $replaces)
{
    file_put_contents(
        $target,
        strtr(
            file_get_contents($target),
            $replaces
        )
    );
}

foreach (glob('tmpl/files/*', GLOB_BRACE) as $distFile) {
    $target = false === strpos($distFile, '-dist')
        ? pathinfo($distFile, PATHINFO_BASENAME)
        : substr(pathinfo($distFile, PATHINFO_FILENAME), 0, -5) . '.' . pathinfo($distFile, PATHINFO_EXTENSION);
    echo "Copy $distFile to $target..." . PHP_EOL;
    copy($distFile, $target);

    echo "Preparing defaults file $target..." . PHP_EOL;
    replaceCustoms($target, $replaces);
}

echo "Creating src/ and tests/ directory" . PHP_EOL;
mkdir('src');
mkdir('tests');

function removeTemplate($path)
{
    foreach (glob($path . '/*') as $value) {
        if (in_array($value, ['.', '..']) && !preg_match('/^\.\w+$/', $value)) {
            continue;
        }
        is_dir($value) ? removeTemplate($value) : unlink($value);
    }

    rmdir($path);
}

echo "Checking docker..." . PHP_EOL;
exec('docker --version', $out, $code);
if (0 !== $code) {
    echo "It is recommended that you have installed docker.";
}
exec('docker-compose --version', $out, $code);
if (0 !== $code) {
    echo "It is recommended that you have installed docker-compose.";
}

echo "Project setup finished..." . PHP_EOL;

echo "Removing template files..." . PHP_EOL;
removeTemplate('tmpl');
