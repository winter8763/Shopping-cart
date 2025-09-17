<!doctype html>
<html>
<head><meta charset="utf-8"><title>Create Product</title></head>
<body>
    <h1>Create Product</h1>
    <form action="{{ route('products.store') }}" method="POST">
        @csrf
        <div><label>Name <input name="name" value="{{ old('name') }}"></label></div>
        <div><label>Description <textarea name="description">{{ old('description') }}</textarea></label></div>
        <div><label>Price <input name="price" value="{{ old('price') }}"></label></div>
        <div><label>Stock <input name="stock" value="{{ old('stock',0) }}"></label></div>
        <div><label for="category_id">Category</label>
            <select id="category_id" name="category_id">
                <option value="">-- 無分類 --</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" {{ old('category_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div><label>Image URL <input name="image_url" value="{{ old('image_url') }}"></label></div>
        <button type="submit">Save</button>
    </form>
    <a href="{{ route('products.index') }}">Back</a>
</body>
</html>