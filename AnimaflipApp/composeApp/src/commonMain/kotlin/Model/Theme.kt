package Model

import androidx.compose.ui.graphics.painter.Painter

data class Theme(
    val id: Int,
    val name: String,
    val animations: List<Painter>
)