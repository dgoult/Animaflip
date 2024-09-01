package Model
import kotlinx.serialization.Serializable

@Serializable
data class Animation(
    val id: Int,
    val theme_id: Int,
    val libelle: String,
    val video_url: String
)