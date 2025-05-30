<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Crear Producto</title>
    <link href="../../assets/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="p-4">
    <div class="container">
        <h1>Crear Producto</h1>
        {% if error %}
            <div class="alert alert-danger">{{ error }}</div>
        {% endif %}
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Nombre</label>
                <input type="text" id="name" name="name" class="form-control" required value="{{ data.name|default('') }}">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Descripción</label>
                <textarea id="description" name="description" class="form-control" required>{{ data.description|default('') }}</textarea>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Precio (€)</label>
                <input type="number" step="0.01" id="price" name="price" class="form-control" required value="{{ data.price|default('') }}">
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Categoría</label>
                <select id="category" name="category" class="form-select" required>
                    <option value="" disabled {{ data.category is not defined or data.category == '' ? 'selected' : '' }}>Selecciona una categoría</option>
                    <option value="hombre" {{ data.category == 'hombre' ? 'selected' : '' }}>Hombre</option>
                    <option value="mujer" {{ data.category == 'mujer' ? 'selected' : '' }}>Mujer</option>
                    <option value="todos" {{ data.category == 'todos' ? 'selected' : '' }}>Todos</option>
                </select>
            </div>
            <div class="mb-3">
    <label for="image_file" class="form-label">Imagen</label>
    <input type="file" id="image_file" name="image_file" class="form-control" accept="image/*">
</div>
            <button type="submit" class="btn btn-primary">Crear</button>
            <a href="/admin/editar-productos" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>
