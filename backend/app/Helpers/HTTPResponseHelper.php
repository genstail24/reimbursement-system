<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class HTTPResponseHelper
{

    /**
     * @param $data
     * @param string $message
     * @param int $status_code
     * @return JsonResponse
     */
    public function toJson($data, string $message = 'Sukses', int $status_code = 200): JsonResponse
    {
        $merge = false;
        $data = collect($data)->toArray();
        if (isset($data['data'])) {
            $merge = true;
        }

        if ($merge) {
            $result = collect(['message' => $message]);
            $result = $result->merge($data);
        } else {
            $result = [
                'message' => $message,
                'data' => $data
            ];
        }
        return response()->json($result, $status_code);
    }

    /**
     * @param $data
     * @param string $message
     * @return JsonResponse
     */
    public function success($data, string $message = 'sukses'): JsonResponse
    {
        return $this->toJson($data, $message, 200);
    }

    /**
     * @param $data
     * @param string $message
     * @param bool $merge
     * @return JsonResponse
     */
    public function created($data, string $message = 'Data berhasil dibuat', bool $merge = false): JsonResponse
    {
        return $this->toJson($data, $message, 201);
    }

    /**
     * @param $data
     * @param string $message
     * @return JsonResponse
     */
    public function failed($data, string $message = 'Gagal'): JsonResponse
    {
        return $this->toJson($data, $message, 401);
    }

    /**
     * @param $request
     * @param $data
     * @return JsonResponse
     */
    public function error($request, $data): JsonResponse
    {
        if ($data instanceof ModelNotFoundException) {
            return $this->notFound();
        }

        $message = $data->errorInfo ?? [$data->getMessage()];
        $msg = implode(' ', $message);

//        Log::handleError($request, $data);

        //MySQL
        if (str_contains($msg, 'Cannot delete or update a parent row')) {
            $msg = "Data sudah dipakai, tidak dapat dihapus";
        }

        //Postgres
        if (str_contains($msg, 'update or delete on table')) {
            $msg = "Data sudah dipakai, tidak dapat dihapus";
        }

        try {
            return $this->toJson([
                'data' => [],
                'errors' => [
                    'trace' => (bool)env('APP_DEBUG') === true ? $data->getTraceAsString() : [],
                ]
            ], $msg, 400);
        } catch (\Exception|\Error $e) {
            return $this->toJson([
                'data' => [],
                'errors' => $data
            ], $msg, 400);
        }
    }

    /**
     * @param string $message
     * @return JsonResponse
     */
    public function notFound(string $message = 'Data tidak ditemukan'): JsonResponse
    {
        return $this->toJson([], $message, 404);
    }

    /**
     * @param $data
     * @param string $message
     * @return JsonResponse
     */
    public function validation($data, string $message = 'Validation Error'): JsonResponse
    {
        return $this->toJson(['data' => [], 'errors' => $data], $message, 422);
    }
}
