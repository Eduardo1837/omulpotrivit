<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit a Review</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f8fafc; color: #1e293b; }
        .form-container { background: white; padding: 25px 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); max-width: 600px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; font-size: 14px; }
        select, textarea { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; font-family: inherit; font-size: 14px; }
        select:focus, textarea:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2); }
        .btn { display: inline-block; padding: 10px 16px; background: #2563eb; color: white; text-decoration: none; border: none; border-radius: 4px; font-size: 14px; cursor: pointer; }
        .btn:hover { background: #1d4ed8; }
        .btn-secondary { background: #64748b; }
        .btn-secondary:hover { background: #475569; }
        .error-message { color: #ef4444; font-size: 12px; margin-top: 6px; display: block; }
        .is-invalid { border-color: #ef4444; }
        .is-invalid:focus { border-color: #ef4444; box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2); }
    </style>
</head>
<body>
    <h1>Submit a Review</h1>

    <div class="form-container">
        <form action="{{ route('reviews.store') }}" method="POST">
            @csrf

            {{-- Select Listing --}}
            <div class="form-group">
                <label for="listing_id">Listing</label>
                <select name="listing_id" id="listing_id" class="{{ $errors->has('listing_id') ? 'is-invalid' : '' }}" required>
                    <option value="">-- Select Listing --</option>
                    @foreach($listings as $listing)
                        <option value="{{ $listing->id }}" {{ old('listing_id') == $listing->id ? 'selected' : '' }}>
                            {{ $listing->title ?? 'Listing #' . $listing->id }}
                        </option>
                    @endforeach
                </select>
                @error('listing_id')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- Rating Field --}}
            <div class="form-group">
                <label for="rating">Rating (1 to 5)</label>
                <select name="rating" id="rating" class="{{ $errors->has('rating') ? 'is-invalid' : '' }}" required>
                    <option value="">-- Select Rating --</option>
                    @for($i = 1; $i <= 5; $i++)
                        <option value="{{ $i }}" {{ old('rating') == $i ? 'selected' : '' }}>
                            {{ $i }} Star{{ $i > 1 ? 's' : '' }}
                        </option>
                    @endfor
                </select>
                @error('rating')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            {{-- Comment Field --}}
            <div class="form-group">
                <label for="comment">Comment</label>
                <textarea name="comment" id="comment" rows="4" class="{{ $errors->has('comment') ? 'is-invalid' : '' }}" required>{{ old('comment') }}</textarea>
                @error('comment')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-top: 30px;">
                <button type="submit" class="btn">Submit</button>
                <a href="{{ route('reviews.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>