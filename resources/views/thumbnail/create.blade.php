@extends('layouts.app')
@section('content')
<div class="Polaris-Page">
    <div class="Polaris-Page__Header">
        <div class="Polaris-Page__Title">
            <h1 class="Polaris-DisplayText">Create Thumbnail Request</h1>
        </div>
    </div>

    <div class="Polaris-Page__Content">
        <div class="Polaris-Card">
            <div class="Polaris-Card__Section">
                <form action="{{ route('thumbnail.store') }}" method="POST">
                    @csrf

                    <div class="Polaris-FormLayout">
                        <div class="Polaris-FormLayout__Item">
                            <div class="Polaris-Labelled__LabelWrapper">
                                <label class="Polaris-Label" for="image_urls">Image URLs (one per line)</label>
                            </div>

                            <textarea
                                id="image_urls"
                                name="image_urls"
                                rows="10"
                                cols="80"
                                required
                                aria-required="true"
                                class="Polaris-TextArea__Input"
                                placeholder="https://example.com/image1.jpg
https://example.com/image2.jpg
https://example.com/image3.jpg">{{ old('image_urls') }}</textarea>

                            @error('image_urls')
                                <div class="Polaris-InlineError">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="Polaris-FormLayout__Item">
                            <button type="submit" class="Polaris-Button Polaris-Button--variantPrimary Polaris-Button--toneCritical">
                                <span class="Polaris-Button__Content">Submit for Processing</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection