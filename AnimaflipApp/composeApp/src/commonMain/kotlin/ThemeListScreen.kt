import Model.ConnectedUser
import Model.Theme
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material.Button
import androidx.compose.material.ButtonDefaults
import androidx.compose.material.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.text.TextStyle
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color

@Composable
fun ThemeListScreen(
    themes: List<Theme>?,
    errorMessage: String?,
    connectedUser: ConnectedUser,
    onThemeSelected: (Theme) -> Unit,
    onLogout: () -> Unit,
    onAdminPanel: () -> Unit
) {
    LazyColumn(modifier = Modifier.fillMaxSize()) {
        item {
            if (connectedUser.user.role == "admin") {
                // Panneau admin
                Button(
                    onClick = onAdminPanel,
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(8.dp),
                    colors = ButtonDefaults.buttonColors(backgroundColor = Color.Green)
                ) {
                    Text(text = "Panneau Administrateur", style = TextStyle(fontSize = 20.sp, color = Color.White))
                }
            }

            // Bouton Se déconnecter
            Button(
                onClick = onLogout,
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(8.dp),
                colors = ButtonDefaults.buttonColors(backgroundColor = Color.Red)
            ) {
                Text(text = "Se déconnecter", style = TextStyle(fontSize = 20.sp, color = Color.White))
            }

            // En-tête avec les informations de l'utilisateur connecté
            Text(
                text = "Vous êtes connecté !",
                style = TextStyle(fontSize = 24.sp),
                textAlign = TextAlign.Center,
                modifier = Modifier.fillMaxWidth().padding(16.dp)
            )
            Text(
                text = connectedUser.user.username,
                style = TextStyle(fontSize = 20.sp),
                textAlign = TextAlign.Center,
                modifier = Modifier.fillMaxWidth().padding(8.dp)
            )
        }

        item {
            Spacer(modifier = Modifier.height(16.dp))
        }

        if (themes != null) {
            items(themes) { theme ->
                if (theme.animations.isNotEmpty()) {
                    ThemeItem(theme = theme, onClick = { onThemeSelected(theme) })
                }
            }
        } else {
            item {
                errorMessage?.let {
                    Text(
                        text = it,
                        color = Color.Red,
                        style = TextStyle(fontSize = 16.sp),
                        textAlign = TextAlign.Center,
                        modifier = Modifier.fillMaxWidth().padding(8.dp)
                    )
                }
            }
        }
    }
}

@Composable
fun ThemeItem(theme: Theme, onClick: () -> Unit) {
    Button(
        onClick = onClick,
        modifier = Modifier
            .fillMaxWidth()
            .height(70.dp)
            .padding(top = 16.dp)
    ) {
        Text(text = theme.libelle, style = TextStyle(fontSize = 20.sp))
    }
}