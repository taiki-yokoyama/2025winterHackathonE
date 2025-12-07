// Progress Chart using Chart.js - 成長が視覚的に分かる折れ線グラフ

document.addEventListener("DOMContentLoaded", function () {
  const canvas = document.getElementById("progressChart");
  if (!canvas) return;

  const ctx = canvas.getContext("2d");

  // Get evaluation data from global variable
  const data = typeof evaluationData !== "undefined" ? evaluationData : [];

  if (data.length === 0) {
    // Show placeholder message
    ctx.font = "20px sans-serif";
    ctx.fillStyle = "#9CA3AF";
    ctx.textAlign = "center";
    ctx.fillText("評価データがありません", canvas.width / 2, canvas.height / 2);
    return;
  }

  // Prepare data for Chart.js
  const labels = data.map((item) => {
    const date = new Date(item.date);
    return `${date.getMonth() + 1}/${date.getDate()}`;
  });

  const scores = data.map((item) => item.score);

  // Calculate trend (increasing, stable, or decreasing)
  let trend = "stable";
  if (scores.length >= 2) {
    const recentScores = scores.slice(-3); // Last 3 scores
    const avgRecent =
      recentScores.reduce((a, b) => a + b, 0) / recentScores.length;
    const avgAll = scores.reduce((a, b) => a + b, 0) / scores.length;

    if (avgRecent > avgAll + 0.5) {
      trend = "increasing";
    } else if (avgRecent < avgAll - 0.5) {
      trend = "decreasing";
    }
  }

  // Set colors based on trend
  let lineColor, gradientColorStart, gradientColorEnd;
  if (trend === "increasing") {
    lineColor = "#10B981"; // Green
    gradientColorStart = "rgba(16, 185, 129, 0.3)";
    gradientColorEnd = "rgba(16, 185, 129, 0.05)";
  } else if (trend === "decreasing") {
    lineColor = "#F59E0B"; // Orange
    gradientColorStart = "rgba(245, 158, 11, 0.3)";
    gradientColorEnd = "rgba(245, 158, 11, 0.05)";
  } else {
    lineColor = "#3B82F6"; // Blue
    gradientColorStart = "rgba(59, 130, 246, 0.3)";
    gradientColorEnd = "rgba(59, 130, 246, 0.05)";
  }

  // Create gradient
  const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height);
  gradient.addColorStop(0, gradientColorStart);
  gradient.addColorStop(1, gradientColorEnd);

  // Create chart
  new Chart(ctx, {
    type: "line",
    data: {
      labels: labels,
      datasets: [
        {
          label: "チームスコア",
          data: scores,
          borderColor: lineColor,
          backgroundColor: gradient,
          borderWidth: 3,
          fill: true,
          tension: 0.4, // Smooth curves
          pointRadius: 6,
          pointHoverRadius: 8,
          pointBackgroundColor: lineColor,
          pointBorderColor: "#FFFFFF",
          pointBorderWidth: 2,
          pointHoverBackgroundColor: lineColor,
          pointHoverBorderColor: "#FFFFFF",
          pointHoverBorderWidth: 3,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        mode: "index",
        intersect: false,
      },
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          backgroundColor: "rgba(255, 255, 255, 0.95)",
          titleColor: "#1F2937",
          bodyColor: "#4B5563",
          borderColor: "#E5E7EB",
          borderWidth: 1,
          padding: 12,
          displayColors: false,
          callbacks: {
            title: function (context) {
              const index = context[0].dataIndex;
              const evaluation = data[index];
              const date = new Date(evaluation.date);
              return `${date.getFullYear()}年${
                date.getMonth() + 1
              }月${date.getDate()}日`;
            },
            label: function (context) {
              return `スコア: ${context.parsed.y}/10`;
            },
            afterLabel: function (context) {
              const index = context.dataIndex;
              const evaluation = data[index];
              const reflection =
                evaluation.reflection.length > 80
                  ? evaluation.reflection.substring(0, 80) + "..."
                  : evaluation.reflection;
              return [
                `投稿者: ${evaluation.username}`,
                "",
                `振り返り:`,
                reflection,
              ];
            },
          },
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          max: 10,
          ticks: {
            stepSize: 1,
            color: "#6B7280",
            font: {
              size: 12,
            },
          },
          grid: {
            color: "#E5E7EB",
            drawBorder: false,
          },
        },
        x: {
          ticks: {
            color: "#6B7280",
            font: {
              size: 12,
            },
          },
          grid: {
            display: false,
            drawBorder: false,
          },
        },
      },
    },
  });

  // Display average score
  const avgScoreElement = document.getElementById("avgScore");
  if (avgScoreElement) {
    const avgScore = (
      scores.reduce((a, b) => a + b, 0) / scores.length
    ).toFixed(1);
    avgScoreElement.textContent = avgScore;
  }
});
