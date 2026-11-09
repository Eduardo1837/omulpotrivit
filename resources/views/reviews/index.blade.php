<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reviews Index</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f8fafc; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background-color: #3b82f6; color: white; }
        tr:hover { background-color: #f1f5f9; }
        .btn { display: inline-block; padding: 6px 12px; background: #2563eb; color: white; text-decoration: none; border-radius: 4px; font-size: 13px; }
        .alert-success { background: #dcfce3; color: #166534; padding: 12px 15px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #bbf7d0; font-size: 14px; }
        .pagination-container { margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Reviews</h1>
    
    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ route('reviews.create') }}" class="btn" style="background: #16a34a; margin-bottom: 15px;">+ Write a Review</a>

    <table>
        <thead>
            <tr>
                <th>Listing</th>
                <th>Author</th>
                <th>Rating</th>
                <th>Comment</th>
                <th>Posted</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reviews as $review)
                <tr>
                    <td><strong>{{ $review->listing->title ?? 'Listing #' . $review->listing_id }}</strong></td>
                    <td>{{ $review->author->name ?? 'Unknown Author' }}</td>
                    <td><strong>{{ $review->rating }} / 5 ★</strong></td>
                    <td>{{ $review->comment }}</td>
                    <td>{{ $review->created_at->diffForHumans() }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No reviews found yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination-container">
        {{ $reviews->links() }}
    </div>
</body>
</html>