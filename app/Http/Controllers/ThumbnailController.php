<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ThumbnailRequest;
use App\Models\ThumbnailJob;
use App\Services\ThumbnailProcessorService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ThumbnailController extends Controller
{
    protected $processorService;

    public function __construct(ThumbnailProcessorService $processorService)
    {
        $this->processorService = $processorService;

        Validator::extend('valid_url', function ($attribute, $value, $parameters, $validator) {
            return Str::startsWith($value, ['http://', 'https://']) && filter_var($value, FILTER_VALIDATE_URL);
        });
    }

    public function index(Request $request)
    {
        $status = $request->get('status');
        
        $thumbnailRequests = ThumbnailRequest::with(['user', 'jobs'])
            ->where('user_id', auth()->id())
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('thumbnail.index', compact('thumbnailRequests'));
    }

    public function create()
    {
        return view('thumbnail.create');
    }

    public function store(Request $request)
    {
        $raw = $request->input('image_urls', '');
        $lines = preg_split("/\r\n|\n|\r/", $raw);
        $urls = array_map('trim', $lines);
        $urls = array_filter($urls, fn($url) => $url !== '');

        $validator = Validator::make(
            ['image_urls' => $urls],
            ['image_urls.*' => 'required|valid_url']
        );

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = auth()->user();
        $maxImages = $user->max_images;

        if (count($urls) > $maxImages) {
            return redirect()->back()
                ->withErrors(["You can only process up to {$maxImages} images per request."])
                ->withInput();
        }

        $thumbnailRequest = ThumbnailRequest::create([
            'user_id' => $user->id,
            'image_urls' => $urls,
            'total_images' => count($urls),
            'status' => 'pending'
        ]);

        $this->processorService->processRequest($thumbnailRequest);

        return redirect()->route('thumbnail.show', $thumbnailRequest->id)
            ->with('success', 'Your thumbnail request has been submitted for processing.');
    }

    public function show($id)
    {
        $thumbnailRequest = ThumbnailRequest::with(['jobs', 'user'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return view('thumbnail.show', compact('thumbnailRequest'));
    }

    public function getJobs($requestId, Request $request)
    {
        $thumbnailRequest = ThumbnailRequest::where('user_id', auth()->id())
            ->findOrFail($requestId);

        $status = $request->get('status');

        $jobs = ThumbnailJob::where('request_id', $requestId)
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $payload = $jobs->map(function ($job) {
            $arr = $job->toArray();
            if ($job->processed_at) {
                $arr['processed_at'] = $job->processed_at instanceof \Illuminate\Support\Carbon
                    ? $job->processed_at->format('Y-m-d H:i:s')
                    : date('Y-m-d H:i:s', strtotime($job->processed_at));
            } else {
                $arr['processed_at'] = null;
            }
            return $arr;
        });

        return response()->json($payload);
    }
}
