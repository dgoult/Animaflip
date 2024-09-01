package Model

import kotlinx.serialization.Serializable

@Serializable
data class EditUser(
    val id: Int,
    var username: String,
    var password: String?,
    var role: String
)