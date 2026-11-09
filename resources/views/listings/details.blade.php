<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Detalii Anunț #{{ $listing->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f8fafc; }
        .card { max-width: 600px; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; color: #334155; }
        input[type="text"], textarea, select { width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; background: #f8fafc; }
        .btn-back { display: inline-block; margin-top: 15px; padding: 8px 16px; background: #64748b; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Detalii Anunț (Details)</h2>

        <!-- Casete Text -->
        <div class="form-group">
            <label>Titlu:</label>
            <input type="text" value="{{ $listing->title }}" readonly>
        </div>

        <div class="form-group">
            <label>Descriere:</label>
            <textarea rows="3" readonly>{{ $listing->description }}</textarea>
        </div>

        <div class="form-group">
            <label>Preț & Oraș:</label>
            <input type="text" value="{{ $listing->priceFormatted }} ({{ $listing->city }})" readonly>
        </div>

        <!-- Listă de selecție (Dropdown) -->
        <div class="form-group">
            <label>Categorie (Listă de selecție):</label>
            <select disabled>
                <option selected>{{ $listing->categoryName }}</option>
            </select>
        </div>

        <!-- Control CheckBox -->
        <div class="form-group">
            <label>
                <input type="checkbox" disabled {{ $listing->isFeatured ? 'checked' : '' }}>
                Anunț Promovat (is_featured)
            </label>
        </div>

        <a href="{{ route('listings.index') }}" class="btn-back">&larr; Înapoi la Index</a>
        <a href="{{ route('listings.edit', $listing->id) }}" class="btn-back" style="background: #f59e0b;">Editează</a>
    </div>
</body>
</html>