import Model.ConnectedUser
import Service.ApiService

class Auth(private val apiService: ApiService) {
    suspend fun login(email: String, password: String): ConnectedUser? {
        return apiService.login(email, password)
    }
}