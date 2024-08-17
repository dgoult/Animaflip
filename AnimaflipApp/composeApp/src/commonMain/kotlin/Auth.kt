import Model.User
import Service.ApiService

class Auth(private val apiService: ApiService) {
    fun login(email: String, password: String): User? {
        val result = apiService.login(email = email, password = password)
        return if (!result) {
            User(email = "admin@admin.com", firstname = "Dylan", lastname = "GOULT")
        } else {
            null
        }
    }
}