<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e(config('app.name', 'Retail_Sm')); ?></title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-b from-slate-50 to-slate-100">
            <div>
                <a href="/">
                    <div class="text-2xl font-semibold text-slate-900">
                        <?php echo e(config('app.name')); ?>

                    </div>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6">
                <div class="ui-card">
                    <div class="ui-card-body">
                        <?php echo e($slot); ?>

                    </div>
                </div>
            </div>
        </div>

        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    </body>
</html>
<?php /**PATH C:\Users\FOHSOH DIRAN\Desktop\Software_Devlopment_Projects\Laravel Projects\Cshop\resources\views/layouts/guest.blade.php ENDPATH**/ ?>