<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Adăugare Anunț Nou (Create)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f8fafc; }
        .card { max-width: 600px; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; color: #334155; }
        input[type="text"], input[type="number"], textarea, select { width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; }
        .btn-save { background: #16a34a; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 15px; }
        .btn-cancel { color: #64748b; margin-left: 15px; text-decoration: none; }
        .checkbox-label { display: flex; align-items: center; gap: 8px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Adăugare Anunț Nou (Create)</h2>

        <form action="{{ route('listings.store') }}" method="POST">
            @csrf <!-- Token CSRF obligatoriu în Laravel -->

            <!-- 1. Casete Text -->
            <div class="form-group">
                <label for="title">Titlu Anunț (Text):</label>
                <input type="text" id="title" name="title" required value="{{ old('title') }}">
            </div>

            <div class="form-group">
                <label for="description">Descriere (Text):</label>
                <textarea id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
            </div>

            <div class="form-group">
                <label for="price">Preț (Numeric/Text):</label>
                <input type="number" step="0.01" id="price" name="price" required value="{{ old('price') }}">
            </div>

            <div class="form-group">
                <label for="city">Oraș (Text):</label>
                <input type="text" id="city" name="city" required value="{{ old('city') }}">
            </div>

            <!-- 2. Listă de Selecție (Dropdown) -->
            <div class="form-group">
                <label for="category_id">Categorie (Listă de Selecție):</label>
                <select id="category_id" name="category_id" required>
                    <option value="">-- Alege Categoria --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="priceType">Tip Preț (Listă de Selecție):</label>
                <select id="priceType" name="priceType" required>
                    <option value="fix">Fix</option>
                    <option value="pe ora">Pe oră</option>
                    <option value="negociabil">Negociabil</option>
                </select>
            </div>

            <!-- 3. Control CheckBox -->
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                    <span>Marchează ca Anunț Promovat (is_featured)</span>
                </label>
            </div>

            <button type="submit" class="btn-save">Salvează (Save)</button>
            <a href="{{ route('listings.index') }}" class="btn-cancel">Anulează</a>
        </form>
    </div>
</body>
</html>