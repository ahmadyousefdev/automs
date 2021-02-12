# Automs

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
<!-- [![Build Status][ico-travis]][link-travis] -->

This tool allows you to create a fully functional component for Laravel using its Jetstream dashboard by one line. Watch [this video](https://youtu.be/fAWRMLBYqRA) to see how you can install it and how it works.

## Requirements

Automs requires 3 main packages in order to work properly

1. Laravel framework `^8`
2. laravel jetstream `^2`
3. livewire `^2`

## Installation

Via Composer

``` bash
$ composer require "ahmadyousefdev/automs" --dev
```

We included `Jetstream` and `livewire` in the composer installer

you have to complete the installation of these two packages by running these commands
``` bash
php artisan jetstream:install livewire
npm install
npm run dev
php artisan migrate
php artisan vendor:publish --tag=jetstream-views
```
to learn more about `Jetstream` and `Livewire` and how to install them properly please go to this [link](https://jetstream.laravel.com/2.x/installation.html).

## Usage

basically, you will write the desired model name and this package will generate it by running this command
``` bash
php artisan automs:create modelName
```
After that you can check the migration file and if everything is alright you should run
``` bash
php artisan migrate
```

## Example

Let's create a component named `Article`, we will do that by just writing this command
``` bash
php artisan automs:create article
```
if we run this command, the package will generate the next files
```
app/Models/Article.php
app/Http/Controllers/ArticleController.php
database/migrations/timestamp_create_articles_table.php
resources/views/articles/index.blade.php
resources/views/articles/create.blade.php
resources/views/articles/show.blade.php
resources/views/articles/edit.blade.php
```
and it will add those routes to `routes/web.php` :
``` php
use App\Http\Controllers\ArticleController;
Route::group(['prefix' => 'articles', 'middleware' => ['auth']], function () {
    Route::get('/', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('/create', [ArticleController::class, 'create'])->name('articles.create');
    Route::post('/submit',[ArticleController::class, 'store'])->name('articles.store');
    Route::get('/id_{id}', [ArticleController::class, 'show'])->name('articles.show');
    Route::get('/id_{id}/edit',[ArticleController::class, 'edit'])->name('articles.edit');
    Route::put('/id_{id}/update',[ArticleController::class, 'update'])->name('articles.update');
    Route::put('/id_{id}/destroy',[ArticleController::class, 'destroy'])->name('articles.destroy');
});
```
and will add a navigation link for this model to `resources/views/navigation-menu.blade.php`

Those files will either be filled with data or they will have the minimal look, depending on the name of the article, please read `How does it work` section for more details

## How does it work ?

This package have a set of built-in laravel component definitions that are connected to multiple names. When running the command, the package will search for the name of the written model in its component list. If it finds that name, it will generate its files. If it didn't find any component, it will generate the files but without the fillables and the migration rows.

A full list of the built-in components can be found inside this [json file](src/model_names.json)

## Notes

if there is a file uploader in any component, it will use the default public disk as a storage. go to your `config/filesystems.php` to configure the storage, or if you are comfortable with the existing settings you can run `php artisan storage:link` to connect the public path to the storage path.

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Contributing

Please see [contributing.md](contributing.md) for details.

## Security

If you discover any security related issues, please email ahmedyousefdev@gmail.com instead of using the issue tracker.

## Credits

- [Ahmed Yousef](https://twitter.com/AhmadYousefDev)

## License

MIT. Please see the [license file](license.md) for more information.

## Acknowledgements
### Influencer References
These two packages inspired us to make this package and they provide much more commands which makes them suitable for more complex applications. Sadly, they don't support Laravel 8 or Jetstream yet.
- [CrestApps/laravel-code-generator](https://github.com/CrestApps/laravel-code-generator)
- [laravel-shift/blueprint](https://github.com/laravel-shift/blueprint)
### Other links
- [Laravel Package Development](https://youtu.be/ivrc1ZKFgHI)
- [Laravel 8 Livewire Tutorial](https://youtu.be/Ub6FMEWw7kA)

[ico-version]: https://img.shields.io/packagist/v/ahmadyousefdev/automs.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/ahmadyousefdev/automs.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/ahmadyousefdev/automs/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/ahmadyousefdev/automs
[link-downloads]: https://packagist.org/packages/ahmadyousefdev/automs
[link-travis]: https://travis-ci.org/ahmadyousefdev/automs
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/ahmadyousefdev
