<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Editar Producto</title>
    <link href="../../assets/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="p-4">
    <div class="container">
        <h1>Editar Producto</h1>
        {% if error %}
            <div class="alert alert-danger">{{ error }}</div>
        {% endif %}
        <form method="POST" action="">
            <div class="mb-3">
                <label for="name" class="form-label">Nombre</label>
                <input type="text" id="name" name="name" class="form-control" required value="{{ data.name }}">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Descripción</label>
                <textarea id="description" name="description" class="form-control" required>{{ data.description }}</textarea>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Precio (€)</label>
                <input type="number" step="0.01" id="price" name="price" class="form-control" required value="{{ data.price }}">
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Categoría</label>
                <select id="category" name="category" class="form-select" required>
                    <option value="hombre" {{ data.category == 'hombre' ? 'selected' : '' }}>Hombre</option>
                    <option value="mujer" {{ data.category == 'mujer' ? 'selected' : '' }}>Mujer</option>
                    <option value="todos" {{ data.category == 'todos' ? 'selected' : '' }}>Todos</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="image_path" class="form-label">URL Imagen (opcional)</label>
                <input type="text" id="image_path" name="image_path" class="form-control" value="{{ data.image_path }}">
            </div>
            <button type="submit" class="btn btn-warning">Guardar cambios</button>
            <a href="/admin/editar-productos" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>
