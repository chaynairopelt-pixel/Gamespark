<!doctype html>
<html lang="nl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>GameSpark | Mijn games</title>
    <link rel="stylesheet" href="css/styles.css" />
  </head>
  <body>
    <main>
      <?php include __DIR__ . "/header.php"; ?>

      <p id="message" role="alert" hidden></p>

      <section class="library-heading">
        <div>
          <p class="eyebrow">JOUW GAMEBIBLIOTHEEK</p>
          <h2>Jouw collectie</h2>
        </div>
        <p id="game-count" class="game-count"></p>
      </section>

      <section class="filters" aria-label="Games filteren en sorteren">
        <label>Platform
          <select id="filter-platform">
            <option value="">Alle platforms</option>
          </select>
        </label>
        <label>Genre
          <select id="filter-genre">
            <option value="">Alle genres</option>
          </select>
        </label>
        <label>Status
          <select id="filter-status">
            <option value="">Alle statussen</option>
            <option value="Wishlist">Wil ik spelen</option>
            <option value="In bezit">In bezit</option>
            <option value="Speelt nu">Speel ik nu</option>
            <option value="Uitgespeeld">Uitgespeeld</option>
          </select>
        </label>
        <label>Sorteren
          <select id="sort-games">
            <option value="title">Naam</option>
            <option value="release_year">Jaar</option>
            <option value="rating">Rating</option>
          </select>
        </label>
      </section>

      <section class="library">
        <div id="games" class="game-grid"></div>
        <p id="empty-state">Je bibliotheek is leeg. Voeg je eerste game toe.</p>
      </section>
    </main>

    <div id="modal" hidden>
      <section class="dialog" role="dialog" aria-modal="true" aria-labelledby="form-title">
        <div class="dialog-heading">
          <h2 id="form-title">Game toevoegen</h2>
          <button id="close" type="button" aria-label="Sluiten">×</button>
        </div>

        <form id="game-form">
          <label>Game titel
            <input name="title" required maxlength="160" />
          </label>

          <div class="two-columns">
            <label>Platform
              <input name="platform" list="platforms" required />
              <datalist id="platforms">
                <option>PC</option>
                <option>PS5</option>
                <option>PS4</option>
                <option>Xbox</option>
                <option>Xbox Series X/S</option>
                <option>Xbox One</option>
                <option>Nintendo Switch</option>
                <option>Nintendo Switch 2</option>
                <option>Steam Deck</option>
                <option>Mobile</option>
              </datalist>
            </label>
            <label>Genre
              <input name="genre" list="genres" required />
              <datalist id="genres">
                <option>Action</option>
                <option>Adventure</option>
                <option>RPG</option>
                <option>Strategy</option>
                <option>Simulation</option>
              </datalist>
            </label>
          </div>

          <div class="two-columns">
            <label>Status
              <select name="status" required>
                <option value="">Kies status</option>
                <option value="Wishlist">Wil ik spelen</option>
                <option value="In bezit">In bezit</option>
                <option value="Speelt nu">Speel ik nu</option>
                <option value="Uitgespeeld">Uitgespeeld</option>
              </select>
            </label>
            <label>Jaar
              <input name="release_year" type="number" min="1950" max="2100" required />
            </label>
          </div>

          <label>Rating
            <select name="rating" required>
              <option value="1">1 / 5</option>
              <option value="2">2 / 5</option>
              <option value="3" selected>3 / 5</option>
              <option value="4">4 / 5</option>
              <option value="5">5 / 5</option>
            </select>
          </label>

          <label>Afbeelding van je apparaat
            <input name="cover_image" type="file" accept="image/jpeg,image/png,image/webp,image/gif" />
          </label>

          <label>Notities
            <textarea name="notes" rows="3" maxlength="280"></textarea>
          </label>

          <p id="form-error" role="alert" hidden></p>
          <div class="buttons">
            <button id="cancel" class="secondary" type="button">Annuleren</button>
            <button type="submit">Opslaan</button>
          </div>
        </form>
      </section>
    </div>

    <div id="details-modal" hidden>
      <section class="dialog" role="dialog" aria-modal="true" aria-labelledby="details-title">
        <div class="dialog-heading">
          <h2 id="details-title"></h2>
          <button id="close-details" type="button" aria-label="Sluiten">×</button>
        </div>
        <img id="details-image" class="details-image" alt="" />
        <p><strong>Platform:</strong> <span id="details-platform"></span></p>
        <p><strong>Genre:</strong> <span id="details-genre"></span></p>
        <p><strong>Status:</strong> <span id="details-status"></span></p>
        <p><strong>Jaar:</strong> <span id="details-year"></span></p>
        <p><strong>Rating:</strong> <span id="details-rating"></span></p>
        <p><strong>Notities:</strong> <span id="details-notes"></span></p>
      </section>
    </div>

    <script src="js/script.js"></script>
  </body>
</html>
