<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Requests\UpdateNewsRequest;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * News API Controller
 * Haber CRUD operasyonları ve arama işlemlerini yönetir
 */
class NewsController extends Controller {
    private ImageService $imageService;

    public function __construct(ImageService $imageService) {
        $this->imageService = $imageService;
    }

    /**
     * Haberlerin listesini getir
     * Sayfalama ve aktif haber filtrelemesi ile
     * Cache desteği ile optimize edilmiş performans
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse {
        try {
            $perPage = $request->get('per_page', 15);
            $perPage = min($perPage, 100); // Maksimum 100 kayıt
            $page = $request->get('page', 1);

            // Cache key oluştur
            $cacheKey = "news_list_page_{$page}_per_page_{$perPage}";

            $news = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($perPage) {
                return News::active()
                    ->with([]) // Eager loading gerekirse eklenebilir
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage);
            });

            return response()->json([
                'success' => true,
                'message' => 'Haberler başarıyla getirildi',
                'data' => $news->map(function ($item) {
                    return $item->toApiArray();
                }),
                'pagination' => [
                    'current_page' => $news->currentPage(),
                    'last_page' => $news->lastPage(),
                    'per_page' => $news->perPage(),
                    'total' => $news->total(),
                    'from' => $news->firstItem(),
                    'to' => $news->lastItem(),
                    'has_more_pages' => $news->hasMorePages(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Haberler getirilirken hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Yeni haber oluştur
     *
     * @param StoreNewsRequest $request
     * @return JsonResponse
     */
    public function store(StoreNewsRequest $request): JsonResponse {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();

            // Görsel varsa işle
            /** @var \Illuminate\Http\Request $request */
            if ($request->hasFile('image')) {
                $imagePath = $this->imageService->processAndStore(
                    $request->file('image'),
                    'images'
                );
                $validatedData['image'] = $imagePath;
            }

            // Haberi oluştur
            $news = News::create($validatedData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Haber başarıyla oluşturuldu',
                'data' => $news->toApiArray()
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            // Eğer görsel yüklendiyse sil
            if (isset($imagePath)) {
                $this->imageService->deleteImage($imagePath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Haber oluşturulurken hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Belirli bir haberi getir
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse {
        try {
            $news = News::active()->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Haber başarıyla getirildi',
                'data' => $news->toApiArray()
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Haber bulunamadı'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Haber getirilirken hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Haberi güncelle
     *
     * @param UpdateNewsRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(UpdateNewsRequest $request, string $id): JsonResponse {
        DB::beginTransaction();

        try {
            $news = News::findOrFail($id);
            $validatedData = $request->validated();
            $oldImagePath = $news->image;

            // Yeni görsel yüklendiyse işle
            /** @var \Illuminate\Http\Request $request */
            if ($request->hasFile('image')) {
                $imagePath = $this->imageService->processAndStore(
                    $request->file('image'),
                    'images'
                );
                $validatedData['image'] = $imagePath;
            }

            // Haberi güncelle
            $news->update($validatedData);

            // Eski görseli sil (yeni görsel yüklendiyse)
            if ($request->hasFile('image') && $oldImagePath) {
                $this->imageService->deleteImage($oldImagePath);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Haber başarıyla güncellendi',
                'data' => $news->fresh()->toApiArray()
            ], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Haber bulunamadı'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();

            // Eğer yeni görsel yüklendiyse sil
            if (isset($imagePath)) {
                $this->imageService->deleteImage($imagePath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Haber güncellenirken hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Haberi sil
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse {
        DB::beginTransaction();

        try {
            $news = News::findOrFail($id);
            $imagePath = $news->image;

            // Haberi sil
            $news->delete();

            // Görseli sil
            if ($imagePath) {
                $this->imageService->deleteImage($imagePath);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Haber başarıyla silindi'
            ], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Haber bulunamadı'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Haber silinirken hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Haberlerde arama yap
     * Başlık ve içerik üzerinden full-text search
     * MySQL fulltext index kullanarak optimize edilmiş performans
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse {
        try {
            $request->validate([
                'query' => 'required|string|min:2|max:100',
                'per_page' => 'nullable|integer|min:1|max:100',
                'status' => 'nullable|in:active,inactive'
            ]);

            $query = $request->get('query');
            $perPage = $request->get('per_page', 15);
            $status = $request->get('status', 'active');

            // MySQL fulltext search kullan (index'ler eklendikten sonra)
            $newsQuery = News::query()
                ->when($status, function ($q) use ($status) {
                    return $q->where('status', $status);
                });

            // Fulltext search - MySQL 5.7+ MATCH AGAINST
            if (strlen($query) >= 2) {
                $newsQuery->whereRaw('MATCH(title, content) AGAINST(? IN BOOLEAN MODE)', [$query . '*'])
                    ->orWhere('title', 'LIKE', "%{$query}%")
                    ->orWhere('content', 'LIKE', "%{$query}%");
            }

            $news = $newsQuery
                ->orderByRaw('MATCH(title, content) AGAINST(? IN BOOLEAN MODE) DESC', [$query . '*'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Arama sonuçları başarıyla getirildi',
                'query' => $query,
                'total_found' => $news->total(),
                'data' => $news->map(function ($item) {
                    return $item->toApiArray();
                }),
                'pagination' => [
                    'current_page' => $news->currentPage(),
                    'last_page' => $news->lastPage(),
                    'per_page' => $news->perPage(),
                    'total' => $news->total(),
                    'from' => $news->firstItem(),
                    'to' => $news->lastItem(),
                    'has_more_pages' => $news->hasMorePages(),
                ]
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz arama parametreleri',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Arama işlemi sırasında hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
