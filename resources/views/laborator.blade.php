<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Baza de Date - Laborator</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f9; }
        .card { background: white; padding: 15px; margin-bottom: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1, h2 { color: #333; }
        .badge { background: #007bff; color: white; padding: 3px 8px; border-radius: 12px; font-size: 12px; }
    </style>
</head>
<body>

   <h2>EAGER LOADING (with listings preîncărcate):</h2>
@foreach($categoriesEager as $cat)
    <div class="card">
        <strong>{{ $cat->name }}</strong> — {{ $cat->listings->count() }} anunțuri
        {{-- listings deja în memorie, 0 query-uri noi --}}
    </div>
@endforeach

<h2>LAZY LOADING (listings încărcate la acces):</h2>
@foreach($categoriesLazy as $cat)
    <div class="card">
        <strong>{{ $cat->name }}</strong> — {{ $cat->listings->count() }} anunțuri
        {{-- fiecare ->listings face un query nou în baza de date --}}
    </div>
@endforeach

<h2>Anunțuri din categoria ID=1 (via ListingAccessor):</h2>
@foreach($listingsFromCategory as $listing)
    <div class="card">{{ $listing->title }}</div>
@endforeach

</body>
</html>