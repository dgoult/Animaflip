import Model.ConnectedUser
import Model.EditUser
import Model.Theme
import Model.User
import Service.ApiService
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.height
import androidx.compose.material.Button
import androidx.compose.material.MaterialTheme
import androidx.compose.material.Text
import androidx.compose.material.TextField
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.unit.dp
import org.jetbrains.compose.ui.tooling.preview.Preview
import androidx.compose.runtime.Composable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.material.CircularProgressIndicator
import kotlinx.coroutines.launch

@Composable
@Preview
fun App() {
    MaterialTheme {
        var connectedUser: ConnectedUser? by remember { mutableStateOf<ConnectedUser?>(null) }
        var selectedTheme: Theme? by remember { mutableStateOf<Theme?>(null) }
        var selectedEditUser: EditUser? by remember { mutableStateOf<EditUser?>(null) }
        val apiService = remember { ApiService() }
        var themes by remember { mutableStateOf<List<Theme>?>(emptyList()) }
        var users by remember { mutableStateOf<List<User>>(emptyList()) }
        val coroutineScope = rememberCoroutineScope()
        var errorMessage by remember { mutableStateOf<String?>(null) }
        var isInAdminPanel by remember { mutableStateOf(false) }

        LaunchedEffect(connectedUser) {
            if (connectedUser != null) {
                val themesResult = apiService.getThemesByUserId(connectedUser!!.token, connectedUser!!.user.id)

                themesResult.fold(
                    onSuccess = { fetchedThemes ->
                        themes = fetchedThemes
                        errorMessage = null // Réinitialiser le message d'erreur en cas de succès
                    },
                    onFailure = { error ->
                        errorMessage = error.message // Stocker le message d'erreur
                        themes = null // Réinitialiser les thèmes en cas d'erreur
                    }
                )

                if (connectedUser!!.user.role == "admin") {
                    val usersResult = apiService.getAllUsers(connectedUser!!.token)
                    usersResult.fold(
                        onSuccess = { fetchedUsers ->
                            users = fetchedUsers
                            errorMessage = null // Réinitialiser le message d'erreur en cas de succès
                        },
                        onFailure = { error ->
                            errorMessage = error.message // Stocker le message d'erreur
                        }
                    )
                }
            }
        }

        when {
            connectedUser == null -> {
                LoginScreen(
                    onLoginSuccess = { user ->
                        connectedUser = user
                    }
                )
            }
            isInAdminPanel -> {
                AdminPanelScreen(
                    apiService = apiService,
                    authToken = connectedUser!!.token,
                    users = users,
                    selectedUser = selectedEditUser,
                    onBack = {
                        coroutineScope.launch {
                            val result = apiService.getThemesByUserId(connectedUser!!.token, connectedUser!!.user.id)
                            result.fold(
                                onSuccess = { updatedThemes ->
                                    themes = updatedThemes
                                    isInAdminPanel = false
                                },
                                onFailure = { error ->
                                    errorMessage = "Erreur lors de la récupération des thèmes : ${error.message}"
                                    isInAdminPanel = false
                                }
                            )
                        }
                    },
                    onEditUser = { userId ->
                        val user = users.find { it.id == userId }
                        if (user != null) {
                            selectedEditUser = EditUser(user.id, user.username, null, user.role)
                        }
                    },
                    onDeleteUser = { userId ->
                        coroutineScope.launch {
                            val result = apiService.deleteUser(connectedUser!!.token, userId)
                            if (result.isSuccess) {
                                users = users.filterNot { it.id == userId }
                            } else {
                                errorMessage = "Erreur lors de la suppression de l'utilisateur."
                            }
                        }
                    },
                    onSaveUser = { editUser ->
                        coroutineScope.launch {
                            val result = apiService.updateUser(connectedUser!!.token, editUser)

                            result.fold(
                                onSuccess = { user -> user as User
                                    if (editUser.id == 0) {
                                        users = users + user
                                    } else {
                                        users = users.map { if (it.id == user.id) user else it }
                                    }
                                    selectedEditUser = null
                                },
                                onFailure = { error ->
                                    errorMessage = "Erreur lors de la sauvegarde de l'utilisateur : ${error.message}"
                                }
                            )
                        }
                    },
                    onUsersUpdated = { updatedUsers ->
                        users = updatedUsers
                    },
                    onSelectTheme = { theme ->
                        selectedTheme = theme
                    }
                )
            }
            selectedTheme == null -> {
                ThemeListScreen(
                    themes = themes,
                    errorMessage = errorMessage,
                    connectedUser = connectedUser!!,
                    onThemeSelected = { theme -> selectedTheme = theme },
                    onLogout = { connectedUser = null },
                    onAdminPanel = { isInAdminPanel = true }
                )
            }
            else -> {
                ThemeDetailScreen(
                    theme = selectedTheme!!,
                    connectedUser = connectedUser!!,
                    onBack = { selectedTheme = null }
                )
            }
        }
    }
}


@Composable
fun LoginScreen(onLoginSuccess: (ConnectedUser) -> Unit) {
    var userEmail by remember { mutableStateOf("") }
    var userPassword by remember { mutableStateOf("") }
    var errorMessage by remember { mutableStateOf<String?>(null) }
    var isLoading by remember { mutableStateOf(false) }
    val coroutineScope = rememberCoroutineScope()
    val apiService = remember { ApiService() }

    LaunchedEffect(isLoading) {
        if (isLoading) {
            try {
                val user = apiService.login(userEmail, userPassword)
                if (user != null) {
                    onLoginSuccess(user)
                } else {
                    errorMessage = "Email ou mot de passe incorrect."
                }
            } catch (e: Exception) {
                errorMessage = "Une erreur est survenue : ${e.message}"
            } finally {
                isLoading = false
            }
        }
    }

    Column(
        modifier = Modifier.fillMaxSize(),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        TextField(
            value = userEmail,
            onValueChange = {
                userEmail = it
                errorMessage = null
            },
            label = { Text("Email") },
            isError = errorMessage != null
        )
        Spacer(modifier = Modifier.height(10.dp))
        TextField(
            value = userPassword,
            onValueChange = {
                userPassword = it
                errorMessage = null
            },
            label = { Text("Mot de passe") },
            visualTransformation = PasswordVisualTransformation(),
            isError = errorMessage != null
        )
        Spacer(modifier = Modifier.height(10.dp))

        errorMessage?.let {
            Text(
                text = it,
                color = MaterialTheme.colors.error,
                style = MaterialTheme.typography.body2,
                modifier = Modifier.padding(vertical = 8.dp)
            )
        }

        Button(
            onClick = {
                if (!isLoading) {
                    isLoading = true
                }
            },
            enabled = !isLoading,
            modifier = Modifier.fillMaxWidth().padding(horizontal = 40.dp)
        ) {
            if (isLoading) {
                CircularProgressIndicator(
                    color = MaterialTheme.colors.onPrimary,
                    modifier = Modifier.size(24.dp)
                )
            } else {
                Text("Se connecter")
            }
        }
    }
}