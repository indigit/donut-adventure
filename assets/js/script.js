'use strict';
window.addEventListener('DOMContentLoaded', function () {

  /* API */
  if (window.wp_api_data == null) {
    return;
  }

  const nonce = window.wp_api_data.wp_nonce;
  const restURI = window.wp_api_data.donut_rest;
  
  getHighScores();

  /* DOM */
  const app         = document.getElementById('app');
  const scoreboard  = document.querySelector('.app-stat_table');
  const intro       = app.querySelector('.app-overlay__intro');
  const settings    = app.querySelector('.app-overlay__settings');
  const annotation  = app.querySelector('div.annotation-wrapper');
  const gameCounter = app.querySelector('.game-counter');
  const winCounter  = app.querySelectorAll('.win-counter');
  const jumpCounter = app.querySelector('.jump-counter');
  const gameTimer   = app.querySelector('.game-timer');

  /* Donut */
  // const donut = {
  window.donut = {
    el: document.querySelector('svg.donut'),
    inRace: false,
    get autorun () {
      return document.getElementById('donut-autorun').checked;
    },
    get totalGames () {
      return this._totalGames === undefined ? 0 : this._totalGames;
    },
    set totalGames (val) {
      this._totalGames = val;
      gameCounter.textContent = val;
    },
    get totalWins () {
      return this._totalWins === undefined ? 0 : this._totalWins;
    },
    set totalWins (val) {
      this._totalWins = val;
      winCounter.forEach(e => e.textContent = val);
    },
    get totalJumps () {
      return this._totalJumps === undefined ? 0 : this._totalJumps;
    },
    set totalJumps (val) {
      this._totalJumps = val;
      jumpCounter.textContent = val;
    },
    get secondsPassed () {
      return this._secondsPassed === undefined ? 0 : this._secondsPassed;
    },
    set secondsPassed (val) {
      this._secondsPassed = val;
      const time = new Date(val * 1000).toISOString().substring(11, 19);
      gameTimer.textContent = time;
    },
    stat: {
      rockPos: 0,
      rockSize: 0,
      time: 0,
      jumpPos: 0,
      won: 0,
    },
    resetStat: function () {
      for (const key in this.stat) {
        if (Object.hasOwnProperty.call(this.stat, key)) {
          this.stat[key] = 0;
        }
      }
    },
    updateStat: function () {
      this.stat.won = annotation.textContent === 'Yay!';
      this.stat.won && this.totalWins++;
      this.stat.time = Date.now() - this.stat.time;
      this.totalGames++;
      sendResult(this.stat);
      this.resetStat();
      if (this.autorun) {
        window.character.run();
      }
    },
    justReseted: false,
  };

  const donutObserver = new MutationObserver(donutObserverCallback);
  donutObserver.observe(donut.el, { attributeFilter: [ 'style' ] });

  setInterval(function() {
    if (!app.classList.contains('blur')) {
      donut.secondsPassed++;
      if (donut.justReseted && donut.secondsPassed > 3) {
        donut.justReseted = false;
      }
    }
  }, 1000);

  /* Start */
  const btnStartGame = document.getElementById('start-game');

  btnStartGame.addEventListener('click', (e) => {
    app.classList.remove('blur');
    app.querySelector('.app-overlay__intro').classList.add('hide');
    window.character.isRunning = false;
    window.character.isJumping = false;
  }, true);
  
  /* Settings */
  const btnGameSettings = document.getElementById('app-settings');
  
  btnGameSettings.addEventListener('click', (e) => {
    window.character.stop();
    app.classList.add('blur');
    settings.classList.remove('hide');
  });

  /* Autorun */
  const autorunSwitch = document.getElementById('donut-autorun');
  const donutControls = app.querySelectorAll('.app-controls > div:not(.app-controls__settings)');

  autorunSwitch.addEventListener('input', e => {
    donutControls.forEach(e => e.classList.toggle('hide'));
  });

  /* Resume */
  const btnGameResume = document.getElementById('game-resume');

  btnGameResume.addEventListener('click', (e) => {
    app.classList.remove('blur');
    settings.classList.add('hide');
    if (donut.autorun) {
      window.character.run();
    }
  });

  /* Reset */
  const btnGameReset = document.getElementById('game-reset');

  btnGameReset.addEventListener('click', (e) => {
    reset();
  });
  
  /* Stop */
  const btnDonutStop = document.getElementById('donut-stop');

  btnDonutStop.addEventListener('click', (e) => {
    if (window.character.isRunning) {
      window.character.stop();
    }
  });
  
  /* Run */
  const btnDonutRun = document.getElementById('donut-run');

  btnDonutRun.addEventListener('click', (e) => {
    if (!window.character.isRunning) {
      window.character.run();
    }
  });

  document.addEventListener('keydown', event => {
    // prevent running
    if (event.code === 'KeyD' && !event.repeat) {
      if (!intro.classList.contains('hide')) {
        window.character.isRunning = true;
      }
    }
  });
  
  /* Jump */
  const btnDonutJump = document.getElementById('donut-jump');

  btnDonutJump.addEventListener('click', (e) => {
    if (!window.character.isJumping) {
      window.character.jump();
      donut.totalJumps++;
    }
  });

  document.addEventListener('keydown', event => {
    if (event.code === 'Space' && !event.repeat && !window.character.isJumping) {
      // prevent jumping
      if (!intro.classList.contains('hide')) {
        window.character.isJumping = true;
      } else {
        donut.totalJumps++;
      }
    }
  });

  /* Functions */

  function donutObserverCallback(mutations, observer) {
    mutations.forEach(mutation => {

      const w = parseInt(window.getComputedStyle(window.character.donut).width);
      const r = window.character.characterPosition + w;

      // in race
      if (!donut.inRace && window.character.characterPosition > 0) {
        donut.inRace = true;
      }
      else if (donut.inRace && window.character.characterPosition === 0) {
        donut.inRace = false;
        setTimeout(() => donut.updateStat(), 20);
      }

      // autojump
      if (
        donut.autorun &&
        window.character.isRunning &&
        !window.character.isJumping &&
        r + 3 > window.terrain.rockPosition &&
        r < window.terrain.rockPosition
      ) {
        window.character.jump();
        donut.totalJumps++;
      }

      // update stat
      if (donut.inRace && window.character.isRunning) {
        if (!donut.stat.jumpPos && window.character.isJumping) {
          donut.stat.jumpPos = window.character.characterPosition;
        }
        if (donut.stat.rockPos === 0) {
          donut.stat.rockPos = window.terrain.rockPosition;
        }
        if (donut.stat.rockSize === 0) {
          donut.stat.rockSize = `${window.terrain.rock.width}x${window.terrain.rock.height}`;
        }
        if (donut.stat.time === 0) {
          donut.stat.time = Date.now();
        }
      }

    });
  }

  function emptyScoreBoard() {
    scoreboard.querySelectorAll('.result')
      .forEach(e => e.remove());
  }

  function fillScoreBoard(data) {
    emptyScoreBoard();
    data.forEach(d => {
      const el = `<div class="app-stat_table__row result"><p>${d.game_date}</p><p>${d.rock_position} px</p><p>${d.rock_size} px</p><p>${d.jump_position}px</p><p>${d.game_time} ms</p><p class="${d.win}"></p></div>`;
      scoreboard.querySelector('.thead').insertAdjacentHTML('beforebegin', el);
    });
  }

  function reset() {
    if (!donut.justReseted) {
      deleteHighScores();
      window.character.stop();
      donut.justReseted = true;
      donut.inRace = false;
      donut.totalGames = 0;
      donut.totalWins = 0;
      donut.totalJumps = 0;
      donut.secondsPassed = 0;
      donut.resetStat();
      window.character.donut.style.left = 0;
    }
  }

  /* GET */
  function getHighScores() {

    const headers = new Headers({
      Accept: 'application/json',
      'X-WP-Nonce': nonce
    });

    const req = new Request(restURI, {
      method: 'GET',
      headers: headers,
    });

    fetch(req)
      .then((response) => {
        if (response.ok) {
          return response.json();
        } else {
          throw `Error ${response.status}`;
        }
      })
      .then((data) => {
        if (data.success) {
          fillScoreBoard(data.data);
        }
      })
      .catch((error) => {
        console.error(error);
      });

  }

  /* POST */
  function sendResult(result) {

    const headers = new Headers({
      Accept: 'application/json',
      'X-WP-Nonce': nonce,
      'Content-Type': 'application/json',
    });

    const req = new Request(restURI, {
      method: 'POST',
      headers: headers,
      body: JSON.stringify(result)
    });

    fetch(req)
      .then((response) => {
        if (response.ok) {
          return response.json();
        } else {
          throw `Error ${response.status}`;
        }
      })
      .then((data) => {
        if (data.success) {
          fillScoreBoard(data.data);
        }
      })
      .catch((error) => {
        console.error(error);
      });

  }

  /* DELETE */
  function deleteHighScores() {

    const headers = new Headers({
      Accept: 'application/json',
      'X-WP-Nonce': nonce
    });

    const req = new Request(restURI, {
      method: 'DELETE',
      headers: headers,
    });

    fetch(req)
      .then((response) => {
        if (response.ok) {
          return response.json();
        } else {
          throw `Error ${response.status}`;
        }
      })
      .then((data) => {
        if (data.success) {
          emptyScoreBoard();
        }
      })
      .catch((error) => {
        console.error(error);
      });
  }

});
