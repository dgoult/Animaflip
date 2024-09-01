package Model
import kotlinx.serialization.Serializable

@Serializable
data class User(
    val id: Int,
    val username: String,
    val role: String
)