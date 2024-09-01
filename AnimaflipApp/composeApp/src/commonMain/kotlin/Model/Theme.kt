package Model

import kotlinx.serialization.Serializable

@Serializable
data class Theme(
    val id: Int,
    val libelle: String,
    val animations: List<Animation>
)