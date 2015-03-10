<?php
/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    // Stages
    //
    // The multiples stages of your application
    // if you don't know what this does, then you don't need it
    //////////////////////////////////////////////////////////////////////

    // Adding entries to this array will split the remote folder in stages
    // Like /var/www/yourapp/staging and /var/www/yourapp/production
    'stages'  => [],

    // The default stage to execute tasks on when --stage is not provided
    // Falsey means all of them
    'default' => '',

];
