<h1 class="mb-4">Editar lançamento</h1>

<form method="POST" action="/financas/public/?url=lancamentos-update">
    <input type="hidden" name="id" value="<?= $lancamento['id'] ?>">

    <div class="row">
        <div class="col-md-4 mb-2">
            <label>Tipo</label>
            <select name="tipo" class="form-select">
                <option value="R" <?= $lancamento['tipo'] == 'R' ? 'selected' : '' ?>>Receita</option>
                <option value="D" <?= $lancamento['tipo'] == 'D' ? 'selected' : '' ?>>Despesa</option>
            </select>
        </div>

        <div class="col-md-4 mb-2">
            <label>Conta</label>
            <select name="id_conta" class="form-select">
                <?php foreach ($contas as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $c['id'] == $lancamento['id_conta'] ? 'selected' : '' ?>>
                        <?= $c['nome'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-4 mb-2">
            <label>Categoria</label>
            <select name="id_categoria" class="form-select">
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $lancamento['id_categoria'] ? 'selected' : '' ?>>
                        <?= $cat['nome'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-4">
            <label>Valor</label>
            <input type="text" name="valor" class="form-control money-br" inputmode="numeric" placeholder="0,00"
                value="<?= number_format((float) $lancamento['valor'], 2, ',', '.') ?>">

        </div>

        <div class="col-md-4">
            <label>Data</label>
            <input type="date" name="data" value="<?= $lancamento['data'] ?>" class="form-control">
        </div>

        <div class="col-md-4">
            <label>Descrição</label>
            <input type="text" name="descricao" value="<?= $lancamento['descricao'] ?>" class="form-control">
        </div>
    </div>

    <button class="btn btn-primary mt-3">Salvar alterações</button>
</form>