<?php
require_once __DIR__ . '/../templates/header.php';
?>
<style>
  body {
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', sans-serif;
    background-image: url('/uploads/Background.png');
    background-size: cover;
    background-position: center;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
  }

  .learnmore-container {
    background-color: rgba(0, 0, 0, 0.7);
    padding: 40px;
    border-radius: 20px;
    max-width: 700px;
    width: 90%;
    text-align: center;
    box-shadow: 0 0 25px rgba(0, 0, 0, 0.5);
  }

  .learnmore-container h1 {
    font-size: 2.2rem;
    margin-bottom: 1rem;
    color: #c2b6f3;
  }

  .learnmore-container p {
    font-size: 1.1rem;
    margin-bottom: 1.5rem;
    line-height: 1.6;
    color: #e4e4e4;
  }

  .learnmore-container ul {
    text-align: left;
    margin: 0 auto 2rem;
    max-width: 550px;
    padding-left: 20px;
  }

  .learnmore-container ul li {
    margin-bottom: 0.8rem;
    list-style: disc;
  }

  .cta-button {
    background-color: #7744dd;
    color: white;
    padding: 12px 25px;
    font-size: 1rem;
    font-weight: bold;
    border: none;
    border-radius: 10px;
    text-decoration: none;
    transition: background-color 0.3s;
    display: inline-block;
  }

  .cta-button:hover {
    background-color: #5f3dc4;
  }
</style>

<div class="learnmore-container">
  <h1>Como funciona a Talentum?</h1>
  <p>Aqui, todos os utilizadores podem contratar e vender serviços. Tu decides o teu papel a qualquer momento!</p>

  <div style="display: flex; flex-wrap: wrap; gap: 2rem; justify-content: center; margin: 2rem 0;">
    <div style="flex: 1; min-width: 250px; background-color: rgba(255, 255, 255, 0.05); padding: 20px; border-radius: 15px;">
      <h2 style="color: #c2b6f3; margin-bottom: 1rem;">👥 Quero contratar</h2>
      <ul style="text-align: left; padding-left: 20px;">
        <li>Explora serviços por categoria ou pesquisa direta</li>
        <li>Analisa perfis, preços e avaliações</li>
        <li>Contrata com um clique e acompanha o progresso</li>
        <li>Avalia a entrega após a conclusão</li>
      </ul>
    </div>

    <div style="flex: 1; min-width: 250px; background-color: rgba(255, 255, 255, 0.05); padding: 20px; border-radius: 15px;">
      <h2 style="color: #c2b6f3; margin-bottom: 1rem;">💼 Quero vender</h2>
      <ul style="text-align: left; padding-left: 20px;">
        <li>Cria e publica os teus serviços com preço e prazo</li>
        <li>Recebe pedidos ou propostas personalizadas</li>
        <li>Entrega o trabalho diretamente pela plataforma</li>
        <li>Recebe na tua carteira Talentum após a aprovação</li>
      </ul>
    </div>
  </div>

  <a class="cta-button" href="register.php">Criar Conta</a>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
