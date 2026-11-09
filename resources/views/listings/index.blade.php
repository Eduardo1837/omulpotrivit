<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Index - Lista Anunțuri</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f8fafc; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background-color: #3b82f6; color: white; }
        tr:hover { background-color: #f1f5f9; }
        .btn { display: inline-block; padding: 6px 12px; background: #2563eb; color: white; text-decoration: none; border-radius: 4px; font-size: 13px; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-featured { background: #fef08a; color: #854d0e; }
    </style>
</head>
<body>
    <h1>Listă Anunțuri (Index)</h1>
    <p>Exercițiu laborator: Afișare entități via <code>ListingAccessor</code> & <code>ListingViewModel</code>.</p>
    <a href="{{ route('listings.create') }}" class="btn" style="background: #16a34a; margin-bottom: 15px;">+ Adaugă Anunț Nou</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Titlu (Text)</th>
                <th>Categorie (Listă Selecție)</th>
                <th>Preț (Text)</th>
                <th>Oraș (Text)</th>
                <th>Promovat (CheckBox)</th>
                <th>Acțiuni</th>
            </tr>
        </thead>
        <tbody>
            @forelse($listings as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td><strong>{{ $item->title }}</strong></td>
                    <td>{{ $item->categoryName }}</td>
                    <td>{{ $item->priceFormatted }}</td>
                    <td>{{ $item->city }}</td>
                    <td>
                        <input type="checkbox" disabled {{ $item->isFeatured ? 'checked' : '' }}>
                        @if($item->isFeatured)
                            <span class="badge badge-featured">Promovat</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('listings.details', $item->id) }}" class="btn">Detalii</a>
                        <a href="{{ route('listings.edit', $item->id) }}" class="btn" style="background: #f59e0b;">Edit</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Nu există înregistrări.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>