document.addEventListener("DOMContentLoaded", () => {

    fetch("../backend/trilha_controller.php?action=dashboard")
        .then(res => res.json())
        .then(data => {

            if (!data.success) {
                console.error("Erro:", data.error);
                return;
            }

            renderTrilhas(data.em_andamento, "grid-minhas-trilhas");
            renderTrilhas(data.recomendados, "grid-recomendados");
            renderTrilhas(data.interesses, "grid-interesse");

        })
        .catch(err => console.error("Erro fetch:", err));

});


function renderTrilhas(lista, containerId) {

    const container = document.getElementById(containerId);

    if (!lista.length) {
        container.innerHTML = "<p>Nenhuma trilha encontrada</p>";
        return;
    }

    container.innerHTML = lista.map(trilha => `

        <div class="card-trilha">

            <img class="card-img" 
                 src="../Imagens/${trilha.img}" 
                 onerror="this.onerror=null; this.src='../Imagens/teste.png'"
                 alt="${trilha.nome}">

            <div class="card-content">

                <h3>${trilha.nome}</h3>

                <p>${trilha.descricao}</p>

                <span class="tag">${trilha.nome_tag ?? "Sem categoria"}</span>

                <div class="card-footer">
                    <span class="status">Em andamento</span>

                    <a class="btn-access" href="trilha_detalhe.html?id=${trilha.id_trilha}">
                        Acessar
                    </a>
                </div>

            </div>

        </div>

    `).join("");
}