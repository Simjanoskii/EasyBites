<?php

declare(strict_types=1);

final class ApiRoutes
{
    public static function registerCrud(string $path, EntityService $service, ApiLogger $logger): void
    {
        Flight::route('GET /api/v1/' . $path, static function () use ($service, $logger): void {
            ApiResponse::run(static function () use ($service): void {
                Flight::json(['data' => $service->findAll()], 200);
            }, $logger);
        });

        Flight::route('GET /api/v1/' . $path . '/@id:[0-9]+', static function ($id) use ($service, $logger): void {
            ApiResponse::run(static function () use ($service, $id): void {
                Flight::json(['data' => $service->findById(ApiRequest::positiveId($id))], 200);
            }, $logger);
        });

        Flight::route('POST /api/v1/' . $path, static function () use ($path, $service, $logger): void {
            ApiResponse::run(static function () use ($path, $service): void {
                $id = $service->create(ApiRequest::jsonObject());
                Flight::response()->header('Location', '/api/v1/' . $path . '/' . $id);
                Flight::json(['data' => ['id' => $id]], 201);
            }, $logger);
        });

        Flight::route('PUT /api/v1/' . $path . '/@id:[0-9]+', static function ($id) use ($service, $logger): void {
            ApiResponse::run(static function () use ($service, $id): void {
                $id = ApiRequest::positiveId($id);
                $service->update($id, ApiRequest::jsonObject());
                Flight::json(['data' => $service->findById($id)], 200);
            }, $logger);
        });

        Flight::route('DELETE /api/v1/' . $path . '/@id:[0-9]+', static function ($id) use ($service, $logger): void {
            ApiResponse::run(static function () use ($service, $id): void {
                $service->delete(ApiRequest::positiveId($id));
                Flight::json(['message' => 'Record deleted.'], 200);
            }, $logger);
        });
    }
}
