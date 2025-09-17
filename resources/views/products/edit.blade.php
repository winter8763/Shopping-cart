<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Edit Product</title>
</head>
<body>
    <h1>Edit Product</h1>

    @if(session('success'))
        <p style="color:green">{{ session('success') }}</p>
    @endif

    @if($errors->any())
        <div style="color:red">
            <ul>
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('products.update', $product) }}" method="POST">
        @csrf
        @method('PUT')

        <div>
            <label for="name">Name</label><br>
            <input id="name" name="name" value="{{ old('name', $product->name) }}" required maxlength="100">
        </div>

        <div>
            <label for="description">Description</label><br>
            <textarea id="description" name="description">{{ old('description', $product->description) }}</textarea>
        </div>

        <div>
            <label for="price">Price</label><br>
            <input id="price" name="price" type="number" step="0.01" value="{{ old('price', $product->price) }}" required>
        </div>

        <div>
            <label for="stock">Stock</label><br>
            <input id="stock" name="stock" type="number" value="{{ old('stock', $product->stock) }}" required>
        </div>

        <div>
            <label for="category_id">Category ID (optional)</label><br>
            <label for="category_id">Category</label>
                <select id="category_id" name="category_id">
                    <option value="">-- 無分類 --</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ old('category_id', $product->category_id) == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
        </div>

        <div>
            <label for="image_url">Image URL (optional)</label><br>
            <input id="image_url" name="image_url" value="{{ old('image_url', $product->image_url) }}">
        </div>

        <div style="margin-top:10px;">
            <button type="submit">Save</button>
            <a href="{{ route('products.show', $product) }}">Cancel</a>
            <a href="{{ route('products.index') }}">Back to list</a>
        </div>
    </form>

    <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete this product?');" style="margin-top:12px;">
        @csrf
        @method('DELETE')
        <button type="submit" style="color:red;">Delete product</button>
    </form>
</body>