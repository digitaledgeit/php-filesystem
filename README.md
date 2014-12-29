# filesystem

Filesystem functions (similar to Symfony... but simplifies copying/moving/deleting search results).

## Installation

    composer require digitaledgeit/filesystem
    
## Usage

    <?php
    
    use deit\filesystem\Finder';
    
    require_once  __DIR__.'/vendor/autoload.php';
    
    //remove all cache files modified more than 15min ago
    $files = Finder::create('./data')
        ->files()
        ->named('/\.cache/')
        ->modified('<', strtotime('15min ago'))
        ->remove()
    ;