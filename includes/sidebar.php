?>
        <nav class="sidebar-nav px-2">
            <a href="index.php?page=dashboard" class="sidebar-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <div class="sidebar-section">Cadastros</div>
            <a href="index.php?page=clients" class="sidebar-link"><i class="bi bi-people"></i> Clientes</a>
            <a href="index.php?page=projects" class="sidebar-link"><i class="bi bi-building"></i> Obras</a>
            <a href="index.php?page=architects" class="sidebar-link"><i class="bi bi-brush"></i> Arquitetos</a>
            <a href="index.php?page=sellers" class="sidebar-link"><i class="bi bi-person-badge"></i> Vendedores</a>
            <a href="index.php?page=estimators" class="sidebar-link"><i class="bi bi-calculator"></i> Projetistas/Orçamentistas</a>
            <a href="index.php?page=categories" class="sidebar-link"><i class="bi bi-tags"></i> Categorias de Produto</a>
            <a href="index.php?page=products" class="sidebar-link"><i class="bi bi-box"></i> Produtos</a>
            <div class="sidebar-section">Orçamentos</div>
            <a href="index.php?page=budgets" class="sidebar-link"><i class="bi bi-file-earmark-text"></i> Orçamentos</a>
            <div class="sidebar-section">Relatórios</div>
            <a href="index.php?page=reports_sales" class="sidebar-link"><i class="bi bi-graph-up"></i> Vendas</a>
            <a href="index.php?page=reports_budgets" class="sidebar-link"><i class="bi bi-clipboard-data"></i> Orçamentos</a>
            <a href="index.php?page=reports_financial" class="sidebar-link"><i class="bi bi-cash-stack"></i> Financeiro</a>
            <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
                <div class="sidebar-section">Administração</div>
                <a href="index.php?page=users" class="sidebar-link"><i class="bi bi-person-gear"></i> Usuários</a>
            <?php endif; ?>
            <a href="logout.php" class="sidebar-link text-danger"><i class="bi bi-box-arrow-left"></i> Sair</a>
        </nav>
    </aside>
