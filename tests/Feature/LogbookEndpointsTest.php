<?php

it('rejects unauthenticated requests to /pireps', function () {
    $this->get('/api/stratos/logbook/pireps')->assertStatus(401);
});

it('rejects unauthenticated requests to /pireps/{id}', function () {
    $this->get('/api/stratos/logbook/pireps/some-id')->assertStatus(401);
});

it('rejects unauthenticated requests to /stats', function () {
    $this->get('/api/stratos/logbook/stats')->assertStatus(401);
});
