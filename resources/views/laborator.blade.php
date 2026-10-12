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

    <h1>Verificare Conexiune Bază de Date - OmulPotrivit</h1>

    <h2>Categorii salvate:</h2>
    <ul>
        @foreach($categories as $category)
            <li><strong>{{ $category->name }}</strong> - {{ $category->description }}</li>
        @endforeach
    </ul>

    <h2>Anunțuri active:</h2>
    @foreach($listings as $listing)
        <div class="card">
            <h3>{{ $listing->title }} <span class="badge">{{ $listing->category->name }}</span></h3>
            <p><strong>Descriere:</strong> {{ $listing->description }}</p>
            <p><strong>Locație:</strong> {{ $listing->city }}</p>
            <p><strong>Tarif:</strong> {{ $listing->price }} RON ({{ $listing->priceType }})</p>
            <hr>
            <p><small>Prestator: {{ $listing->provider->name }} | Contact: {{ $listing->provider->phone }}</small></p>
        </div>
    @endforeach

</body>
</html>