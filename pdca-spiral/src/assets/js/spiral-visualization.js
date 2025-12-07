// Spiral Visualization using Canvas

document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('spiralCanvas');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    // Get evaluation data from global variable
    const data = typeof evaluationData !== 'undefined' ? evaluationData : [];
    
    if (data.length === 0) {
        // Show placeholder message
        ctx.font = '20px sans-serif';
        ctx.fillStyle = '#9CA3AF';
        ctx.textAlign = 'center';
        ctx.fillText('評価データがありません', canvas.width / 2, canvas.height / 2);
        return;
    }
    
    // Canvas dimensions
    const width = canvas.width;
    const height = canvas.height;
    const centerX = width / 2;
    const centerY = height / 2;
    
    // Spiral parameters
    const startRadius = 30;
    const radiusIncrement = 15;
    const angleIncrement = Math.PI / 3; // 60 degrees
    
    // Clear canvas
    ctx.clearRect(0, 0, width, height);
    
    // Draw spiral path
    ctx.beginPath();
    ctx.strokeStyle = '#E5E7EB';
    ctx.lineWidth = 2;
    
    for (let i = 0; i <= data.length; i++) {
        const angle = i * angleIncrement;
        const radius = startRadius + (i * radiusIncrement);
        const x = centerX + radius * Math.cos(angle);
        const y = centerY + radius * Math.sin(angle);
        
        if (i === 0) {
            ctx.moveTo(x, y);
        } else {
            ctx.lineTo(x, y);
        }
    }
    ctx.stroke();
    
    // Draw evaluation points
    data.forEach((evaluation, index) => {
        const angle = index * angleIncrement;
        const radius = startRadius + (index * radiusIncrement);
        const x = centerX + radius * Math.cos(angle);
        const y = centerY + radius * Math.sin(angle);
        
        // Calculate color based on score
        const score = evaluation.score;
        let color;
        if (score >= 8) {
            color = '#10B981'; // Green
        } else if (score >= 5) {
            color = '#3B82F6'; // Blue
        } else {
            color = '#EF4444'; // Red
        }
        
        // Draw point
        ctx.beginPath();
        ctx.arc(x, y, 8, 0, 2 * Math.PI);
        ctx.fillStyle = color;
        ctx.fill();
        ctx.strokeStyle = '#FFFFFF';
        ctx.lineWidth = 2;
        ctx.stroke();
        
        // Draw score label
        ctx.font = 'bold 12px sans-serif';
        ctx.fillStyle = '#1F2937';
        ctx.textAlign = 'center';
        ctx.fillText(score.toString(), x, y - 15);
        
        // Store point data for hover interaction
        canvas.dataset[`point${index}`] = JSON.stringify({
            x, y, evaluation
        });
    });
    
    // Add hover interaction
    canvas.addEventListener('mousemove', function(e) {
        const rect = canvas.getBoundingClientRect();
        const mouseX = e.clientX - rect.left;
        const mouseY = e.clientY - rect.top;
        
        let hoveredPoint = null;
        
        // Check if mouse is over any point
        for (let i = 0; i < data.length; i++) {
            const pointData = JSON.parse(canvas.dataset[`point${i}`]);
            const distance = Math.sqrt(
                Math.pow(mouseX - pointData.x, 2) + 
                Math.pow(mouseY - pointData.y, 2)
            );
            
            if (distance < 10) {
                hoveredPoint = pointData;
                break;
            }
        }
        
        // Update cursor
        canvas.style.cursor = hoveredPoint ? 'pointer' : 'default';
        
        // Show tooltip
        if (hoveredPoint) {
            showTooltip(e, hoveredPoint.evaluation);
        } else {
            hideTooltip();
        }
    });
    
    canvas.addEventListener('mouseleave', hideTooltip);
});

function showTooltip(e, evaluation) {
    let tooltip = document.getElementById('spiralTooltip');
    
    if (!tooltip) {
        tooltip = document.createElement('div');
        tooltip.id = 'spiralTooltip';
        tooltip.className = 'absolute bg-white shadow-lg rounded-lg p-4 border border-gray-200 z-50 max-w-xs';
        document.body.appendChild(tooltip);
    }
    
    tooltip.innerHTML = `
        <div class="font-semibold text-gray-800 mb-2">スコア: ${evaluation.score}/10</div>
        <div class="text-sm text-gray-600 mb-1">投稿者: ${evaluation.username}</div>
        <div class="text-sm text-gray-600 mb-2">${formatDateTime(evaluation.date)}</div>
        <div class="text-sm text-gray-700">${escapeHtml(evaluation.reflection.substring(0, 100))}${evaluation.reflection.length > 100 ? '...' : ''}</div>
    `;
    
    tooltip.style.display = 'block';
    tooltip.style.left = (e.pageX + 10) + 'px';
    tooltip.style.top = (e.pageY + 10) + 'px';
}

function hideTooltip() {
    const tooltip = document.getElementById('spiralTooltip');
    if (tooltip) {
        tooltip.style.display = 'none';
    }
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return `${date.getFullYear()}年${date.getMonth() + 1}月${date.getDate()}日 ${date.getHours()}:${String(date.getMinutes()).padStart(2, '0')}`;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
