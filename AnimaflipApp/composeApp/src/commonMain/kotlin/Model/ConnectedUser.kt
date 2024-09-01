package Model
import kotlinx.serialization.Serializable

@Serializable
data class ConnectedUser(
    val token: String,
    val user: User
)