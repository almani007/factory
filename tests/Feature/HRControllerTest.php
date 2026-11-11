<?php

namespace Tests\Feature;

use Tests\TestCase;

class HRControllerTest extends TestCase
{
    /** @test */
    public function user_can_view_hr_page()
    {
        $response = $this->get('/hr');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('HR/HR')
        );
    }

    /** @test */
    public function user_can_view_payroll()
    {
        $response = $this->get('/hr/payroll');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('HR/Payroll')
        );
    }

    /** @test */
    public function user_can_view_performance_reviews()
    {
        $response = $this->get('/hr/performance');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('HR/PerformanceReviews')
        );
    }

    /** @test */
    public function user_can_view_training()
    {
        $response = $this->get('/hr/training');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('HR/Training')
        );
    }

    /** @test */
    public function user_can_view_training_matrix()
    {
        $response = $this->get('/hr/training-matrix');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('HR/TrainingMatrix')
        );
    }

    /** @test */
    public function user_can_view_time_attendance()
    {
        $response = $this->get('/hr/time-attendance');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('HR/TimeAttendance')
        );
    }

    /** @test */
    public function user_can_view_shifts()
    {
        $response = $this->get('/hr/shifts');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page->component('HR/Shifts')
        );
    }
}
