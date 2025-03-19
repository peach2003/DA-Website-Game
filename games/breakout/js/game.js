class Breakout {
  constructor() {
    this.canvas = document.getElementById("gameCanvas");
    this.ctx = this.canvas.getContext("2d");
    this.score = 0;
    this.gameOver = false;

    // Paddle properties
    this.paddleHeight = 10;
    this.paddleWidth = 75;
    this.paddleX = (this.canvas.width - this.paddleWidth) / 2;

    // Ball properties
    this.ballRadius = 10;
    this.ballX = this.canvas.width / 2;
    this.ballY = this.canvas.height - 30;
    this.ballSpeedX = 3;
    this.ballSpeedY = -3;

    // Brick properties
    this.brickRowCount = 3;
    this.brickColumnCount = 5;
    this.brickWidth = 75;
    this.brickHeight = 20;
    this.brickPadding = 10;
    this.brickOffsetTop = 30;
    this.brickOffsetLeft = 30;

    // Controls
    this.rightPressed = false;
    this.leftPressed = false;

    // Initialize bricks
    this.bricks = [];
    for (let c = 0; c < this.brickColumnCount; c++) {
      this.bricks[c] = [];
      for (let r = 0; r < this.brickRowCount; r++) {
        this.bricks[c][r] = { x: 0, y: 0, status: 1 };
      }
    }

    // Event listeners
    document.addEventListener("keydown", this.keyDownHandler.bind(this));
    document.addEventListener("keyup", this.keyUpHandler.bind(this));
    document.addEventListener("mousemove", this.mouseMoveHandler.bind(this));
    document
      .querySelector(".retry-button")
      .addEventListener("click", this.restart.bind(this));

    this.gameLoop();
  }

  drawBall() {
    this.ctx.beginPath();
    this.ctx.arc(this.ballX, this.ballY, this.ballRadius, 0, Math.PI * 2);
    this.ctx.fillStyle = "#0095DD";
    this.ctx.fill();
    this.ctx.closePath();
  }

  drawPaddle() {
    this.ctx.beginPath();
    this.ctx.rect(
      this.paddleX,
      this.canvas.height - this.paddleHeight,
      this.paddleWidth,
      this.paddleHeight
    );
    this.ctx.fillStyle = "#0095DD";
    this.ctx.fill();
    this.ctx.closePath();
  }

  drawBricks() {
    for (let c = 0; c < this.brickColumnCount; c++) {
      for (let r = 0; r < this.brickRowCount; r++) {
        if (this.bricks[c][r].status === 1) {
          const brickX =
            c * (this.brickWidth + this.brickPadding) + this.brickOffsetLeft;
          const brickY =
            r * (this.brickHeight + this.brickPadding) + this.brickOffsetTop;
          this.bricks[c][r].x = brickX;
          this.bricks[c][r].y = brickY;
          this.ctx.beginPath();
          this.ctx.rect(brickX, brickY, this.brickWidth, this.brickHeight);
          this.ctx.fillStyle = "#0095DD";
          this.ctx.fill();
          this.ctx.closePath();
        }
      }
    }
  }

  collisionDetection() {
    for (let c = 0; c < this.brickColumnCount; c++) {
      for (let r = 0; r < this.brickRowCount; r++) {
        const b = this.bricks[c][r];
        if (b.status === 1) {
          if (
            this.ballX > b.x &&
            this.ballX < b.x + this.brickWidth &&
            this.ballY > b.y &&
            this.ballY < b.y + this.brickHeight
          ) {
            this.ballSpeedY = -this.ballSpeedY;
            b.status = 0;
            this.score += 10;
            this.updateScore();

            if (
              this.score ===
              this.brickRowCount * this.brickColumnCount * 10
            ) {
              this.endGame(true);
            }
          }
        }
      }
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
        game_id: 10, // ID của game Breakout trong database
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

  keyDownHandler(e) {
    if (e.key === "Right" || e.key === "ArrowRight") {
      this.rightPressed = true;
    } else if (e.key === "Left" || e.key === "ArrowLeft") {
      this.leftPressed = true;
    }
  }

  keyUpHandler(e) {
    if (e.key === "Right" || e.key === "ArrowRight") {
      this.rightPressed = false;
    } else if (e.key === "Left" || e.key === "ArrowLeft") {
      this.leftPressed = false;
    }
  }

  mouseMoveHandler(e) {
    const relativeX = e.clientX - this.canvas.offsetLeft;
    if (relativeX > 0 && relativeX < this.canvas.width) {
      this.paddleX = relativeX - this.paddleWidth / 2;
    }
  }

  endGame(won = false) {
    this.gameOver = true;
    document.querySelector(".game-over").style.display = "block";
    document.querySelector(".game-over h2").textContent = won
      ? "You Won!"
      : "Game Over!";
    // Send final score to parent window
    window.parent.postMessage(this.score, "*");
  }

  restart() {
    this.score = 0;
    this.updateScore();
    this.gameOver = false;
    document.querySelector(".game-over").style.display = "none";

    // Reset ball and paddle
    this.ballX = this.canvas.width / 2;
    this.ballY = this.canvas.height - 30;
    this.ballSpeedX = 3;
    this.ballSpeedY = -3;
    this.paddleX = (this.canvas.width - this.paddleWidth) / 2;

    // Reset bricks
    for (let c = 0; c < this.brickColumnCount; c++) {
      for (let r = 0; r < this.brickRowCount; r++) {
        this.bricks[c][r].status = 1;
      }
    }

    this.gameLoop();
  }

  draw() {
    // Clear canvas
    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

    this.drawBricks();
    this.drawBall();
    this.drawPaddle();
    this.collisionDetection();

    // Ball collision with walls
    if (
      this.ballX + this.ballSpeedX > this.canvas.width - this.ballRadius ||
      this.ballX + this.ballSpeedX < this.ballRadius
    ) {
      this.ballSpeedX = -this.ballSpeedX;
    }
    if (this.ballY + this.ballSpeedY < this.ballRadius) {
      this.ballSpeedY = -this.ballSpeedY;
    } else if (
      this.ballY + this.ballSpeedY >
      this.canvas.height - this.ballRadius
    ) {
      if (
        this.ballX > this.paddleX &&
        this.ballX < this.paddleX + this.paddleWidth
      ) {
        this.ballSpeedY = -this.ballSpeedY;
      } else {
        this.endGame();
        return;
      }
    }

    // Paddle movement
    if (
      this.rightPressed &&
      this.paddleX < this.canvas.width - this.paddleWidth
    ) {
      this.paddleX += 7;
    } else if (this.leftPressed && this.paddleX > 0) {
      this.paddleX -= 7;
    }

    // Move ball
    this.ballX += this.ballSpeedX;
    this.ballY += this.ballSpeedY;
  }

  gameLoop() {
    if (!this.gameOver) {
      this.draw();
      requestAnimationFrame(this.gameLoop.bind(this));
    }
  }
}

// Start game when page loads
window.onload = () => new Breakout();


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
