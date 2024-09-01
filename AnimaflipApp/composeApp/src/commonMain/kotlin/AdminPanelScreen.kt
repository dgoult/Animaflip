import Model.EditUser
import Model.User
import Model.Theme
import Service.ApiService
import androidx.compose.foundation.layout.*
import androidx.compose.material.*
import androidx.compose.runtime.*
import androidx.compose.ui.*
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.TextStyle
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import kotlinx.coroutines.launch

@Composable
fun AdminPanelScreen(
    users: List<User>,
    authToken: String,
    apiService: ApiService,
    selectedUser: EditUser?,
    onBack: () -> Unit,
    onEditUser: (Int) -> Unit,
    onDeleteUser: (Int) -> Unit,
    onSaveUser: (EditUser) -> Unit,
    onSelectTheme: (Theme) -> Unit,
) {
    var isNewUser by remember { mutableStateOf(false) }
    var isEditing by remember { mutableStateOf(false) }
    var themesAdminPanel: List<Theme?> by remember { mutableStateOf<List<Theme?>>(emptyList()) }
    var errorMessage by remember { mutableStateOf<String?>(null) }



    Column(
        modifier = Modifier.fillMaxSize(),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Top
    ) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(8.dp),
            horizontalArrangement = Arrangement.Start
        ) {
            Button(
                onClick = onBack,
                colors = ButtonDefaults.buttonColors(backgroundColor = Color.Green)
            ) {
                Text("Retour", style = TextStyle(fontSize = 20.sp, color = Color.White))
            }
        }

        Text(text = "Panneau d'administration", style = TextStyle(fontSize = 24.sp))

        Spacer(modifier = Modifier.height(20.dp))

        if (isEditing && selectedUser != null) {
            EditUserForm(
                editUser = selectedUser,
                onSave = {
                    onSaveUser(it)
                },
                onCancel = {
                    isEditing = false
                }
            )

            LaunchedEffect(themesAdminPanel) {
                val themesResult = apiService.getThemesByUserId(authToken, selectedUser.id)

                themesResult.fold(
                    onSuccess = { fetchedThemes ->
                        themesAdminPanel = fetchedThemes
                        errorMessage = null // Réinitialiser le message d'erreur en cas de succès
                    },
                    onFailure = { error ->
                        themesAdminPanel = emptyList() // Réinitialiser les thèmes en cas d'erreur
                        errorMessage = error.message // Stocker le message d'erreur
                    }
                )
            }

            Spacer(modifier = Modifier.height(20.dp))

            Text(text = "Thèmes associés", style = TextStyle(fontSize = 20.sp))
            LazyColumn(modifier = Modifier.fillMaxHeight()) {
                items(themesAdminPanel) { theme ->
                    ThemeRow(theme = theme!!, onSelectTheme = onSelectTheme)
                }
            }
        } else if (isNewUser) {
            NewUserForm(
                authToken = authToken,
                apiService = apiService,
                onCancel = {
                    isNewUser = false
                }
            )
        } else {
            Button(onClick = { isNewUser = true }) {
                Text("Ajouter un utilisateur")
            }

            Spacer(modifier = Modifier.height(20.dp))

            LazyColumn(modifier = Modifier.fillMaxSize()) {
                items(users) { user ->
                    UserRow(
                        user = user,
                        onEditUser = {
                            onEditUser(user.id)
                            isEditing = true},
                        onDeleteUser = { onDeleteUser(user.id) }
                    )
                }
            }
        }
    }
}

@Composable
fun UserRow(user: User, onEditUser: () -> Unit, onDeleteUser: () -> Unit) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .padding(8.dp),
        horizontalArrangement = Arrangement.SpaceBetween,
        verticalAlignment = Alignment.CenterVertically
    ) {
        Text(text = user.username, style = TextStyle(fontSize = 18.sp))

        Row {
            Button(onClick = onEditUser, modifier = Modifier.padding(end = 8.dp)) {
                Text("Modifier")
            }
            Button(onClick = onDeleteUser, colors = ButtonDefaults.buttonColors(backgroundColor = Color.Red)) {
                Text("Supprimer")
            }
        }
    }
}

@Composable
fun EditUserForm(
    editUser: EditUser,
    onSave: (EditUser) -> Unit,
    onCancel: () -> Unit
) {
    var username by remember { mutableStateOf(editUser.username) }
    var password by remember { mutableStateOf(editUser.password ?: "") }
    var role by remember { mutableStateOf(editUser.role) }

    Column(
        modifier = Modifier.fillMaxWidth().padding(16.dp),
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        TextField(
            value = username,
            onValueChange = { username = it },
            label = { Text("Email") },
            modifier = Modifier.fillMaxWidth().padding(vertical = 8.dp)
        )

        TextField(
            value = password,
            onValueChange = { password = it },
            label = { Text("Mot de passe (laisser vide pour ne pas changer)") },
            visualTransformation = PasswordVisualTransformation(),
            modifier = Modifier.fillMaxWidth().padding(vertical = 8.dp)
        )

        // Sélecteur de rôle
        Text("Rôle:")
        Spacer(modifier = Modifier.height(5.dp))
        Row {
            RadioButton(
                selected = role == "user",
                onClick = { role = "user" }
            )
            Text("Utilisateur")
            Spacer(modifier = Modifier.width(10.dp))
            RadioButton(
                selected = role == "admin",
                onClick = { role = "admin" }
            )
            Text("Administrateur")
        }

        Spacer(modifier = Modifier.height(20.dp))

        Row {
            Button(onClick = {
                editUser.username = username
                editUser.password = if (password.isNotEmpty()) password else null
                editUser.role = role
                onSave(editUser)
            }, modifier = Modifier.weight(1f)) {
                Text("Sauvegarder")
            }

            Spacer(modifier = Modifier.width(16.dp))

            Button(onClick = onCancel, modifier = Modifier.weight(1f), colors = ButtonDefaults.buttonColors(backgroundColor = Color.Gray)) {
                Text("Annuler")
            }
        }
    }
}

@Composable
fun ThemeRow(theme: Theme, onSelectTheme: (Theme) -> Unit) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .padding(8.dp),
        horizontalArrangement = Arrangement.SpaceBetween,
        verticalAlignment = Alignment.CenterVertically
    ) {
        Text(text = theme.libelle, style = TextStyle(fontSize = 18.sp))

        Button(onClick = { onSelectTheme(theme) }) {
            Text("Voir")
        }
    }
}

@Composable
fun NewUserForm(
    apiService: ApiService,
    authToken: String,
    onCancel: () -> Unit
) {
    var username by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var role by remember { mutableStateOf("user") }
    val coroutineScope = rememberCoroutineScope()
    var successMessage by remember { mutableStateOf<String?>(null) }
    var errorMessage by remember { mutableStateOf<String?>(null) }

    Column(
        modifier = Modifier.fillMaxWidth().padding(16.dp),
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        // Formulaire d'enregistrement de l'utilisateur
        TextField(
            value = username,
            onValueChange = { username = it },
            label = { Text("Email") }
        )
        Spacer(modifier = Modifier.height(10.dp))
        TextField(
            value = password,
            onValueChange = { password = it },
            label = { Text("Mot de passe") },
            visualTransformation = PasswordVisualTransformation()
        )
        Spacer(modifier = Modifier.height(10.dp))

        // Sélecteur de rôle
        Text("Rôle:")
        Spacer(modifier = Modifier.height(5.dp))
        Row {
            RadioButton(
                selected = role == "user",
                onClick = { role = "user" }
            )
            Text("User")
            Spacer(modifier = Modifier.width(10.dp))
            RadioButton(
                selected = role == "admin",
                onClick = { role = "admin" }
            )
            Text("Admin")
        }

        Spacer(modifier = Modifier.height(20.dp))

        // Bouton pour soumettre le formulaire
        Button(onClick = {
            coroutineScope.launch {
                val result = apiService.registerUser(authToken, username, password, role)
                result.fold(
                    onSuccess = {
                        successMessage = "Utilisateur enregistré avec succès"
                        errorMessage = null
                    },
                    onFailure = { error ->
                        errorMessage = "Erreur: ${error.message}"
                        successMessage = null
                    }
                )
            }
        }) {
            Text("Enregistrer l'utilisateur")
        }

        Spacer(modifier = Modifier.height(20.dp))

        // Messages de succès ou d'erreur
        successMessage?.let {
            Text(text = it, color = Color.Green, style = TextStyle(fontSize = 16.sp))
        }
        errorMessage?.let {
            Text(text = it, color = Color.Red, style = TextStyle(fontSize = 16.sp))
        }

        Button(onClick = onCancel, colors = ButtonDefaults.buttonColors(backgroundColor = Color.Gray)) {
            Text("Retour à la liste des utilisateurs")
        }

    }
}