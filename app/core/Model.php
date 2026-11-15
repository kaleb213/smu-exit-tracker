<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Base model — marker class for the Model layer of the MVC pattern.
 * Models are static data-access services built on App\Core\Database;
 * all SQL is executed via prepared statements (data layer).
 */
abstract class Model
{
}
