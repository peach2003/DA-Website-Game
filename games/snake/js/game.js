class Snake {
  constructor() {
    this.canvas = document.getElementById("gameCanvas");
    this.ctx = this.canvas.getContext("2d");
    this.score = 0;
    this.gridSize = 20;
    this.snake = [{ x: 10, y: 10 }];
    this.direction = "right";
    this.food = this.generateFood();
    this.gameOver = false;
    this.speed = 150;

    document.addEventListener("keydown", this.handleKeyPress.bind(this));
    document
      .querySelector(".retry-button")
      .addEventListener("click", this.restart.bind(this));

    this.gameLoop();
  }

  generateFood() {
    const x = Math.floor(Math.random() * (this.canvas.width / this.gridSize));
    const y = Math.floor(Math.random() * (this.canvas.height / this.gridSize));
    return { x, y };
  }

  draw() {
    // Clear canvas
    this.ctx.fillStyle = "white";
    this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);

    // Draw snake
    this.ctx.fillStyle = "#4CAF50";
    this.snake.forEach((segment) => {
      this.ctx.fillRect(
        segment.x * this.gridSize,
        segment.y * this.gridSize,
        this.gridSize - 2,
        this.gridSize - 2
      );
    });

    // Draw food
    this.ctx.fillStyle = "red";
    this.ctx.fillRect(
      this.food.x * this.gridSize,
      this.food.y * this.gridSize,
      this.gridSize - 2,
      this.gridSize - 2
    );
  }

  move() {
    const head = { ...this.snake[0] };

    switch (this.direction) {
      case "up":
        head.y--;
        break;
      case "down":
        head.y++;
        break;
      case "left":
        head.x--;
        break;
      case "right":
        head.x++;
        break;
    }

    // Check collision with walls
    if (
      head.x < 0 ||
      head.x >= this.canvas.width / this.gridSize ||
      head.y < 0 ||
      head.y >= this.canvas.height / this.gridSize
    ) {
      this.endGame();
      return;
    }

    // Check collision with self
    if (
      this.snake.some((segment) => segment.x === head.x && segment.y === head.y)
    ) {
      this.endGame();
      return;
    }

    this.snake.unshift(head);

    // Check if snake ate food
    if (head.x === this.food.x && head.y === this.food.y) {
      this.score += 10;
      this.updateScore();
      this.food = this.generateFood();
      // Increase speed
      this.speed = Math.max(50, this.speed - 5);
    } else {
      this.snake.pop();
    }
  }

  handleKeyPress(event) {
    const keyMap = {
      ArrowUp: "up",
      ArrowDown: "down",
      ArrowLeft: "left",
      ArrowRight: "right",
    };

    const newDirection = keyMap[event.key];
    if (!newDirection) return;

    const opposites = {
      up: "down",
      down: "up",
      left: "right",
      right: "left",
    };

    if (opposites[newDirection] !== this.direction) {
      this.direction = newDirection;
    }
  }

  updateScore() {
    document.getElementById("score").textContent = this.score;
    // Gửi điểm về parent window
    if (window.parent) {
      window.parent.postMessage(this.score, "*");
    }
    // Gửi điểm về server
    fetch("/update_score.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        game_id: 9, // ID của game Snake trong database
        score: this.score,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.error) {
          console.error("Lỗi khi cập nhật điểm:", data.error);
        }
      })
      .catch((error) => {
        console.error("Lỗi khi gửi điểm:", error);
      });
  }

  endGame() {
    this.gameOver = true;
    document.querySelector(".game-over").style.display = "block";
    // Send final score to parent window
    window.parent.postMessage(this.score, "*");
  }

  restart() {
    this.snake = [{ x: 10, y: 10 }];
    this.direction = "right";
    this.score = 0;
    this.updateScore();
    this.food = this.generateFood();
    this.gameOver = false;
    this.speed = 150;
    document.querySelector(".game-over").style.display = "none";
    this.gameLoop();
  }

  gameLoop() {
    if (this.gameOver) return;

    this.move();
    this.draw();

    setTimeout(() => this.gameLoop(), this.speed);
  }
}

// Start game when page loads
window.onload = () => new Snake();


// Auto-generated score tracker
window.getScore = function() {
    return typeof score !== 'undefined' ? score : 0;
};

// Score change observer
setInterval(function() {
    var currentScore = window.getScore();
    if (currentScore > 0) {
        window.parent.postMessage(currentScore, '*');
    }
}, 1000);
