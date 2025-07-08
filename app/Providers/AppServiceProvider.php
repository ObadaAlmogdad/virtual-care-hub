<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\Interfaces\MedicalHistoryRepositoryInterface;
use App\Repositories\MedicalHistoryRepository;
use App\Repositories\Interfaces\DoctorRepositoryInterface;
use App\Repositories\DoctorRepository;
use App\Services\DoctorService;
use App\Repositories\Interfaces\QuestionRepositoryInterface;
use App\Repositories\QuestionRepository;
use App\Services\QuestionService;
use App\Repositories\Interfaces\ConsultationRepositoryInterface;
use App\Repositories\ConsultationRepository;
use App\Services\ConsultationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(
            \App\Repositories\Interfaces\DocumentRepositoryInterface::class,
            \App\Repositories\DocumentRepository::class
        );
        $this->app->bind(
            MedicalHistoryRepositoryInterface::class,
            MedicalHistoryRepository::class
        );
        $this->app->bind(DoctorRepositoryInterface::class, DoctorRepository::class);
        $this->app->singleton(DoctorService::class, function ($app) {
            return new DoctorService($app->make(DoctorRepositoryInterface::class));
        });
        $this->app->bind(QuestionRepositoryInterface::class, QuestionRepository::class);
        $this->app->singleton(QuestionService::class, function ($app) {
            return new QuestionService($app->make(QuestionRepositoryInterface::class));
        });
        $this->app->bind(ConsultationRepositoryInterface::class, ConsultationRepository::class);
        $this->app->singleton(ConsultationService::class, function ($app) {
            return new ConsultationService($app->make(ConsultationRepositoryInterface::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
