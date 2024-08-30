package Service

import Model.ConnectedUser
import Model.Theme
import Model.User
import androidx.compose.runtime.Composable
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
import io.ktor.client.request.setBody
import io.ktor.client.statement.HttpResponse

// Assurez-vous d'importer votre package correctement

class ApiService {
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

    suspend fun login(email: String, password: String): ConnectedUser? {
//        return ConnectedUser(token = "token", User(id = 1, username = "test", role = "test"))
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
        } finally {
            client.close()
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
}