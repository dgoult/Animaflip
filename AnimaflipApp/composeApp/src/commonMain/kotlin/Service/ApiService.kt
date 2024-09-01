package Service

import Model.ConnectedUser
import Model.EditUser
import Model.Theme
import Model.User
import io.ktor.client.HttpClient
import io.ktor.client.request.post
import io.ktor.http.ContentType
import io.ktor.http.contentType
import kotlinx.serialization.json.Json
import io.ktor.client.plugins.contentnegotiation.ContentNegotiation
import io.ktor.client.plugins.logging.Logging
import io.ktor.serialization.kotlinx.json.json
import io.ktor.client.call.body
import io.ktor.client.plugins.HttpTimeout
import io.ktor.client.plugins.logging.DEFAULT
import io.ktor.client.plugins.logging.LogLevel
import io.ktor.client.request.get
import io.ktor.client.request.headers
import io.ktor.client.request.setBody
import io.ktor.client.statement.HttpResponse
import io.ktor.client.statement.bodyAsText
import io.ktor.http.isSuccess
import io.ktor.client.request.*

// Assurez-vous d'importer votre package correctement

class ApiService () {
    private val client = HttpClient {
        install(ContentNegotiation) {
            json(Json { ignoreUnknownKeys = true })
        }
        install(Logging) {
            logger = io.ktor.client.plugins.logging.Logger.DEFAULT
            level = LogLevel.BODY
        }
        install(HttpTimeout) {
            requestTimeoutMillis = 10000
        }
    }

    suspend fun isVideoUrlAccessible(url: String): Boolean {
        return try {
            val response: HttpResponse = client.head(url)
            response.status.isSuccess()
        } catch (e: Exception) {
            println("Exception occurred while checking video URL: ${e.message}")
            false
        }
    }

    suspend fun getAllThemes(authToken: String?): Result<List<Theme>> {
        if (authToken == null) {
            return Result.failure(IllegalStateException("User is not authenticated"))
        }

        return try {
            val response: HttpResponse = client.get("http://10.0.2.2/api/themes") {
                headers {
                    append("Authorization", "$authToken")
                }
            }

            if (response.status.isSuccess()) {
                // Désérialiser uniquement si la réponse est réussie
                val themes: List<Theme> = response.body()
                Result.success(themes)
            } else {
                val errorMessage = "Erreur lors de la récupération des thèmes: ${response.bodyAsText()}-${response.status}"
                println(errorMessage)
                Result.failure(Exception(errorMessage))
            }
        } catch (e: Exception) {
            val errorMessage = "Exception lors de la récupération des thèmes: ${e.message}"
            println(errorMessage)
            Result.failure(Exception(errorMessage))
        }
    }

    suspend fun getThemesByUserId(authToken: String?, userId: Int): Result<List<Theme>> {
        if (authToken == null) {
            return Result.failure(IllegalStateException("User is not authenticated"))
        }

        return try {
            val response: HttpResponse = client.get("http://10.0.2.2/api/user/$userId/themes") {
                headers {
                    append("Authorization", "$authToken")
                }
            }

            if (response.status.isSuccess()) {
                // Désérialiser uniquement si la réponse est réussie
                val themes: List<Theme> = response.body()
                Result.success(themes)
            } else {
                val errorMessage = "Erreur lors de la récupération des thèmes: ${response.bodyAsText()}-${response.status}"
                println(errorMessage)
                Result.failure(Exception(errorMessage))
            }
        } catch (e: Exception) {
            val errorMessage = "Exception lors de la récupération des thèmes: ${e.message}"
            println(errorMessage)
            Result.failure(Exception(errorMessage))
        }
    }

    suspend fun assignThemeToUser(authToken: String, userId: Int, themeId: Int): Result<Unit> {
        return try {
            val response: HttpResponse = client.post("http://10.0.2.2/api/user/themes/assign") {
                contentType(ContentType.Application.Json)
                headers {
                    append("Authorization", authToken)
                }
                setBody(mapOf("user_id" to userId, "theme_id" to themeId))
            }

            if (response.status.isSuccess()) {
                Result.success(Unit)
            } else {
                val errorMessage = "Erreur lors de l'affectation du thème: ${response.bodyAsText()} - ${response.status}"
                println(errorMessage)
                Result.failure(Exception(errorMessage))
            }
        } catch (e: Exception) {
            val errorMessage = "Exception lors de l'affectation du thème: ${e.message}"
            println(errorMessage)
            Result.failure(Exception(errorMessage))
        }
    }

    suspend fun unassignThemeFromUser(authToken: String, userId: Int, themeId: Int): Result<Unit> {
        return try {
            val response: HttpResponse = client.post("http://10.0.2.2/api/user/themes/unassign") {
                contentType(ContentType.Application.Json)
                headers {
                    append("Authorization", authToken)
                }
                setBody(mapOf("user_id" to userId, "theme_id" to themeId))
            }

            if (response.status.isSuccess()) {
                Result.success(Unit)
            } else {
                val errorMessage = "Erreur lors de la désaffectation du thème: ${response.bodyAsText()} - ${response.status}"
                println(errorMessage)
                Result.failure(Exception(errorMessage))
            }
        } catch (e: Exception) {
            val errorMessage = "Exception lors de la désaffectation du thème: ${e.message}"
            println(errorMessage)
            Result.failure(Exception(errorMessage))
        }
    }

    suspend fun login(email: String, password: String): ConnectedUser? {
        val response: HttpResponse = client.post("http://10.0.2.2/api/login") {
            contentType(ContentType.Application.Json)
            setBody(mapOf("username" to email, "password" to password))
        }

        return try {
            val connectedUser: ConnectedUser = response.body()
            println("Received user: ${connectedUser.user.username}")
            connectedUser
        } catch (e: Exception) {
            println("Exception occurred: ${e.message}")
            e.printStackTrace()
            null
        }
    }

    suspend fun getAllUsers(authToken: String): Result<List<User>> {
        return try {
            val response: HttpResponse = client.get("http://10.0.2.2/api/users") {
                headers {
                    append("Authorization", authToken)
                }
            }

            if (response.status.isSuccess()) {
                val users: List<User> = response.body()
                Result.success(users)
            } else {
                val errorMessage = "Erreur lors de la récupération des utilisateurs: ${response.bodyAsText()} - ${response.status}"
                println(errorMessage)
                Result.failure(Exception(errorMessage))
            }
        } catch (e: Exception) {
            val errorMessage = "Exception lors de la récupération des utilisateurs: ${e.message}"
            println(errorMessage)
            Result.failure(Exception(errorMessage))
        }
    }

    suspend fun registerUser(authToken: String, username: String, password: String, role: String): Result<Unit> {
        return try {
            val response: HttpResponse = client.post("http://10.0.2.2/api/register") {
                contentType(ContentType.Application.Json)
                headers {
                    append("Authorization", "$authToken")
                }
                setBody(mapOf("username" to username, "password" to password, "role" to role))
            }

            if (response.status.isSuccess()) {
                Result.success(Unit)
            } else {
                val errorMessage = "Erreur lors de l'enregistrement de l'utilisateur: ${response.bodyAsText()} - ${response.status}"
                println(errorMessage)
                Result.failure(Exception(errorMessage))
            }
        } catch (e: Exception) {
            val errorMessage = "Exception lors de l'enregistrement de l'utilisateur: ${e.message}"
            println(errorMessage)
            Result.failure(Exception(errorMessage))
        }
    }

    suspend fun updateUser(authToken: String, editUser: EditUser): Result<User> {
        return try {
            val response: HttpResponse = client.put("http://10.0.2.2/api/user/${editUser.id}") {
                contentType(ContentType.Application.Json)
                headers {
                    append("Authorization", authToken)
                }
                setBody(editUser)
            }

            if (response.status.isSuccess()) {
                val user: User = response.body()
                Result.success(user)
            } else {
                val errorMessage = "Erreur lors de la mise à jour de l'utilisateur: ${response.bodyAsText()} - ${response.status}"
                println(errorMessage)
                Result.failure(Exception(errorMessage))
            }
        } catch (e: Exception) {
            val errorMessage = "Exception lors de la mise à jour de l'utilisateur: ${e.message}"
            println(errorMessage)
            Result.failure(Exception(errorMessage))
        }
    }

    suspend fun deleteUser(authToken: String, userId: Int): Result<Unit> {
        return try {
            val response: HttpResponse = client.delete("http://10.0.2.2/api/user/$userId") {
                headers {
                    append("Authorization", authToken)
                }
            }

            if (response.status.isSuccess()) {
                Result.success(Unit)
            } else {
                val errorMessage = "Erreur lors de la suppression de l'utilisateur: ${response.bodyAsText()} - ${response.status}"
                println(errorMessage)
                Result.failure(Exception(errorMessage))
            }
        } catch (e: Exception) {
            val errorMessage = "Exception lors de la suppression de l'utilisateur: ${e.message}"
            println(errorMessage)
            Result.failure(Exception(errorMessage))
        }
    }
}

//    fun login(email: String, password: String) : Boolean {
//        if(email == "admin@admin.com" && password == "admin")
//        {
//            return true;
//        } else {
//            return false;
//        }
//    }
